<?php

namespace App\Service\Api;

use App\Entity\Payment\Stripe;
use App\Entity\User\Client;
use App\Entity\User\Professional; 
use App\Repository\Payment\StripeRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\TokenGenerator;
use App\Entity\User\Payment\Transfer;
use App\Repository\User\Payment\TransferRepository;

class StripeService extends AbstractApi
{
    private const BASE_URL = 'https://api.stripe.com/v1';

    private const MINIMUM_AMOUNT_EUR = 50;

    private float $amountFees = 5; // % kept by the platform

    private ?Stripe $stripeConfig = null;

    public function __construct(
        HttpClientInterface $client,
        private StripeRepository $stripeRepository,
        private TokenGenerator $tokenGenerator,
        private TransferRepository $transferRepository
    ) {
        parent::__construct($client);
        $this->loadConfiguration();
    }

    private function loadConfiguration(): void
    {
        $config = $this->stripeRepository->findStripe();

        $this->stripeConfig = $config;

        $this->amountFees = $config->getFeesAmount();

        // Configure requests options
        $this->setOptions([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->stripeConfig->getSecretKey(),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
        ]);
    }

    public function getPublicKey()
    {
        return $this->stripeConfig->getPublicKey();
    }

    public function getSecretKey()
    {
        return $this->stripeConfig->getSecretKey();
    }

    public function isActive()
    {
        return $this->stripeConfig->isActive();
    }

    public function getAccounts()
    {
        return $this->sendRequest(self::BASE_URL . '/accounts', 'GET', [], [])->toArray();
    }

    /**
     * get stripe`s account informations
     */
    public function getAccount(): array
    {
        $response = $this->sendRequest(self::BASE_URL . '/account');

        return $response->toArray();
    }

    // =========================================================================
    // Connect — merchant accounts (acct_xxx)
    // =========================================================================

    /**
     * Creates an Express Stripe Connect account for a Professional.
     * The Professional must complete onboarding via createAccountLink().
     */
    public function createConnectAccount(Professional $professional): array
    {
        $response = $this->sendRequest(
            self::BASE_URL . '/accounts',
            'POST',
            [
                'type'         => 'express',
                'country'      => 'FR',
                'email'        => $professional->getEmail(),
                'capabilities' => [
                    'card_payments' => ['requested' => 'true'],
                    'transfers'     => ['requested' => 'true'],
                ],
                'business_profile' => [
                    'name' => $professional->getCompanyName(),
                ],
                'metadata' => [
                    'professional_id' => $professional->getId(),
                ],
            ]
        );

        return $response->toArray();
    }

    /**
     * Generates the Stripe Connect onboarding link for an Express account.
     * Redirect the user to this URL to complete their profile.
     */
    public function createAccountLink(string $accountId, string $refreshUrl, string $returnUrl): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/account_links',
            'POST',
            [
                'account'     => $accountId,
                'refresh_url' => $refreshUrl,
                'return_url'  => $returnUrl,
                'type'        => 'account_onboarding',
            ]
        )->toArray();
    }

    /**
     * Retrieves information about a Connect account.
     */
    public function getConnectAccount(string $accountId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/accounts/' . $accountId
        )->toArray();
    }

    /**
     * Checks whether a Connect account has completed onboarding.
     */
    public function isConnectAccountReady(string $accountId): bool
    {
        $account = $this->getConnectAccount($accountId);

        return $account['charges_enabled'] && $account['payouts_enabled'];
    }

    public function isReady(): bool
    {
        try{
            $this->getAccount();
        } catch(\Exception $e) {
            return false;
        }

        return $this->isActive() === true;
    }

    /**
     * Deletes a Connect account (called by the account.deleted webhook).
     */
    public function deleteConnectAccount(string $accountId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/accounts/' . $accountId,
            'DELETE'
        )->toArray();
    }

    /**
     * ========================
     * Payment methods requests
     * ========================
     */

    public function attachPaymentMethod(string $paymentMethodId, string $stripeCustomerId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/payment_methods/' . $paymentMethodId . '/attach',
            'POST',
            ['customer' => $stripeCustomerId]
        )->toArray();
    }

    /**
     * Detach payment method
     */
    public function detachPaymentMethod(string $paymentMethodId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/payment_methods/' . $paymentMethodId . '/detach',
            'POST'
        )->toArray();
    }

    /**
     * Define default payment method
     */
    public function setDefaultPaymentMethod(string $stripeCustomerId, string $paymentMethodId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/customers/' . $stripeCustomerId,
            'POST',
            ['invoice_settings' => ['default_payment_method' => $paymentMethodId]]
        )->toArray();
    }

    /**
     * All user payment methods
     */
    public function getPaymentMethods(string $stripeCustomerId, string $type = 'card'): array
    {
        $response = $this->sendRequest(
            self::BASE_URL . '/payment_methods',
            'GET',
            ['customer' => $stripeCustomerId, 'type' => $type]
        )->toArray();

        return $response['data'] ?? [];
    }

    /**
     * =========================
     * Payment intents requests
     * =========================
     */

    /**
     * Create Stripe payment
     *
     * @param int    $amount   Amount in cents (ex: 1000 = 10,00 €)
     * @param string $currency Code ISO 4217 (ex: "eur", "usd")
     */
    public function createPaymentIntent(int $amount, string $currency = 'eur'): array
    {
        $response = $this->sendRequest(
            self::BASE_URL . '/payment_intents',
            'POST',
            [
                'amount'   => $amount,
                'currency' => $currency,
            ]
        );

        return $response->toArray();
    }

    public function getPaymentsIntents(): array
    {
        $response = $this->sendRequest(
            self::BASE_URL . '/payment_intents',
            'GET',
            [
                'expand' => ['data.customer', 'data.payment_method'],
            ]
        );

        return $response->toArray();
    }

    public function createPaymentIntentFromItems(
        array  $items,
        string $stripeCustomerId,
        string $currency        = 'eur',
        ?string $paymentMethodId = null,
        ?string $merchantAccountId = null
    ): array {
        if (empty($items)) {
            throw new \InvalidArgumentException('Items list cannot be empty.');
        }

        if (!$paymentMethodId) {
            throw new \RuntimeException('Payment method not found');
        }

        array_walk($items, fn(array &$item) => $this->validateItem($item));

        $total   = $this->calculateTotal($items);

        if ($total < self::MINIMUM_AMOUNT_EUR) {
            throw new \InvalidArgumentException(sprintf(
                'Total amount %d cts is below Stripe minimum of %d cts.',
                $total,
                self::MINIMUM_AMOUNT_EUR
            ));
        }

        $params = [
            'amount'             => $total,
            'currency'           => $currency,
            'customer'           => $stripeCustomerId,
            'setup_future_usage' => 'off_session',
            'description'        => $this->buildDescription($items),
            'automatic_payment_methods[allow_redirects]'      => 'never',
            'automatic_payment_methods[enabled]'              => 'true',
        ];

        $params['payment_method'] = $paymentMethodId;

        $params['metadata[items]'] = json_encode(array_map(
            fn(array $item) => [
                'name'  => $item['name'],
                'qty'   => $item['quantity'],
                'price' => $item['price'],
                'tva'   => $item['tax'] ?? 0.0,
                'merchant' => [
                    'id'   => $item['merchant_account_id'] ?? null,
                    'name' => $item['merchant_name'] ?? null,
                    'adress' => $item['merchant_adress']
                ],
            ],
            $items
        ));

        $params['metadata[total_items]'] = count($items);

        $params['metadata[merchant_account]'] = $merchantAccountId;

        $paymentIntent =  $this->sendRequest(
            self::BASE_URL . '/payment_intents',
            'POST',
            $params
        )->toArray();

        $this->sendRequest(
            self::BASE_URL . '/payment_intents/' . $paymentIntent['id'],
            'POST',
            ['metadata' => ['webhook_token' => $this->tokenGenerator->generate($paymentIntent['id'])]]
        );

        return $paymentIntent;
    }

    public function getPaymentIntent(string $paymentIntentId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/payment_intents/' . $paymentIntentId,
            'GET',
            [
                'expand' => ['customer', 'payment_method', 'transfer_data.destination']
            ]
        )->toArray();
    }

    public function confirmPaymentIntent(string $paymentIntentId, string $paymentMethodId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/payment_intents/' . $paymentIntentId . '/confirm',
            'POST',
            ['payment_method' => $paymentMethodId]
        )->toArray();
    }

    public function getPaymentIntentsByCustomer(string $stripeCustomerId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/payment_intents',
            'GET',
            [
                'customer' => $stripeCustomerId,
                'expand'   => ['data.customer', 'data.payment_method', 'data.latest_charge'],
            ]
        )->toArray();
    }

    public function getPaymentIntentsByMerchant(string $merchantAccountId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/payment_intents',
            'GET',
            ['expand' => ['data.customer', 'data.latest_charge']],
            ['Stripe-Account' => $merchantAccountId]
        )->toArray();
    }

    // =========================================================================
    // Transfers — redistribution multi-marchands après paiement
    // =========================================================================
 
    /**
     * Crée un Transfer vers un compte Connect.
     * À appeler après confirmation du PaymentIntent (webhook payment_intent.succeeded).
     *
     * @param int    $amount            Montant en centimes à reverser
     * @param string $merchantAccountId Compte Connect destination (acct_xxx)
     * @param string $chargeId          ID de la Charge liée au PaymentIntent (ch_xxx)
     */
    public function createTransfer(int $amount, string $merchantAccountId, string $chargeId): array
    {
        return $this->sendRequest(
            self::BASE_URL . '/transfers',
            'POST',
            [
                'amount'             => $amount,
                'currency'           => 'eur',
                'destination'        => $merchantAccountId,
                'source_transaction' => $chargeId,
            ]
        )->toArray();
    }

    /**
     * Distribue le montant d'un PaymentIntent confirmé vers chaque marchand.
     * À appeler depuis le webhook payment_intent.succeeded.
     *
     * Les items doivent contenir 'merchant_account_id', 'price' et 'quantity'.
     * La plateforme conserve $this->amountFees % sur chaque item.
     *
     * @return array Liste des transfers créés
     */
    public function distributePayment(string $paymentIntentId, string $webhookToken): array
    {
        $pi = $this->getPaymentIntent($paymentIntentId);

        if (!$this->tokenGenerator->verify($webhookToken, $paymentIntentId)) {
            throw new \RuntimeException('Invalid or expired webhook token.');
        }
 
        if ($pi['status'] !== 'succeeded') {
            throw new \RuntimeException('PaymentIntent is not succeeded.');
        }
 
        $chargeId = is_array($pi['latest_charge'])
            ? $pi['latest_charge']['id']
            : $pi['latest_charge'];
 
        $items = json_decode($pi['metadata']['items'] ?? '[]', true);
 
        // Calculer le net par marchand après déduction des frais plateforme
        $byMerchant = [];
        foreach ($items as $item) {
            $merchant = $item['merchant'] ?? null;
            
            if (!$merchant) {
                continue;
            }

            // Skip si déjà transféré (protection retry)
            $existing = $this->transferRepository->findOneBy([
                'chargeId'          => $chargeId,
                'accountId' => $merchant['id'],
            ]);

            if ($existing) {
                $transfers[] = $existing;
                continue;
            }

            if(!isset($byMerchant[$merchant['id']])) {
                $byMerchant[$merchant['id']] = 0;
            }
 
            $itemTotal = (float) $item['price'] * (int) $item['qty'] * 100;
            $fee       = $itemTotal * $this->amountFees / 100;
            $net       = $itemTotal - $fee;
 
            $byMerchant[$merchant['id']] = ($byMerchant[$merchant['id']] ?? 0) + $net;
        }
        

        // Créer un Transfer par marchand
        $transfers = [];
        foreach ($byMerchant as $merchantId => $amount) {
            $result = $this->createTransfer($amount, $merchantId, $chargeId);

            $transfer = new Transfer(
                $result['id'],
                $chargeId,
                $merchantId,
                $amount
            );

            $transfers[] = $transfer;
        }
 
        return $transfers;
    }

    /**
     * =======================
     * -- Customer requests --
     * =======================
     */

    public function createCustomer(Client $user)
    {
        
        $params = $this->customerParameters($user);

        $response = $this->sendRequest(
            self::BASE_URL . '/customers',
            'POST',
            $params
        );

        return $response->toArray();
    }

    public function getCustomer(string $id)
    {
        $response = $this->sendRequest(
            self::BASE_URL . '/customers/' . $id,
            'GET'
        );

        return $response->toArray();
    }

    public function updateCustomer(string $id, Client $user)
    {
        $params = $this->customerParameters($user);

        $response = $this->sendRequest(
            self::BASE_URL . '/customers/' . $id,
            'POST',
            $params
        );

        return $response->toArray();
    }

    public function findCustomerByEmail(string $email): ?array
    {
        $response = $this->sendRequest(
            self::BASE_URL . '/customers',
            'GET',
            ['email' => $email, 'limit' => 1]
        )->toArray();

        return $response['data'][0] ?? null;
    }

    private function customerParameters(Client $user)
    {
        return [
            'email' => $user->getEmail(),
            'name' => $user->getFirstname() . ' ' . $user->getLastname()
        ];
    }

    /**
     * =================
     * private functions
     * =================
     */

    private function calculateTotal(array $items): int
    {
        $total = array_sum(array_map(
            static fn(array $item): float => round($item['price'] * $item['quantity'] * 100),
            $items
        ));

        return (int) $total;
    }

    private function buildDescription(array $items): string
    {
        return implode(', ', array_map(
            static fn(array $item): string => sprintf('%s x%d', $item['name'], $item['quantity']),
            $items
        ));
    }

    private function validateItem(array &$item): void
    {
        foreach (['name', 'quantity', 'price'] as $key) {
            if (!isset($item[$key])) {
                throw new \InvalidArgumentException(
                    sprintf('Item is missing required key "%s".', $key)
                );
            }
        }

        if (is_numeric($item['price'])) {
            $item['price'] = (float) $item['price'];
        }

        if (!is_float($item['price']) || $item['price'] <= 0) {
            throw new \InvalidArgumentException(
                sprintf('Item "price" must be a positive number (got "%s").', $item['price'])
            );
        }

        if (!is_int($item['quantity']) || $item['quantity'] <= 0) {
            throw new \InvalidArgumentException('Item "quantity" must be a positive integer.');
        }
    }
}