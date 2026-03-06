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

    public function createCusomer(Client $user)
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

    private function customerParameters(Client $user)
    {
        return [
            'email' => $user->getEmail(),
            'name' => $user->getFirstname() . ' ' . $user->getLastname()
        ];
    }
}