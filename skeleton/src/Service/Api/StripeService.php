<?php

namespace App\Service\Api;

use App\Entity\Payment\Stripe;
use App\Entity\User\Client;
use App\Repository\Payment\StripeRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StripeService extends AbstractApi
{
    private const BASE_URL = 'https://api.stripe.com/v1';

    private ?Stripe $stripeConfig = null;

    public function __construct(HttpClientInterface $client, private StripeRepository $stripeRepository) {
        parent::__construct($client);
        $this->loadConfiguration();
    }

    private function loadConfiguration(): void
    {
        $config = $this->stripeRepository->findStripe();

        $this->stripeConfig = $config;

        // Préconfigure le client avec la clé secrète en auth Bearer
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

    /**
     * Create Stripe payment
     *
     * @param int    $amount   Montant en centimes (ex: 1000 = 10,00 €)
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

    /**
     * get created payment intent
     */
    public function getPaymentIntent(string $paymentIntentId): array
    {
        $response = $this->sendRequest(
            self::BASE_URL . '/payment_intents/' . $paymentIntentId
        );

        return $response->toArray();
    }

    public function getPaymentsIntents(): array
    {
        $response = $this->sendRequest(
            self::BASE_URL . '/payment_intents'
        );

        return $response->toArray();
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

    public function updateCustomer(string $id)
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
}