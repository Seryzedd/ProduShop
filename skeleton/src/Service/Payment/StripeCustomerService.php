<?php

namespace App\Service\Payment;

use App\Entity\User\Payment\StripeCustomer;
use App\Entity\User\Client;
use App\Repository\User\Payment\StripeRepository as StripeCustomerRepository;
use App\Service\Api\StripeService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Responsabilité : faire le lien entre un Client Symfony et son customer Stripe.
 * Orchestre StripeService (API) + Doctrine (persistance).
 * C'est ici que vit toute la logique métier liée au customer.
 */
class StripeCustomerService
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly StripeCustomerRepository $stripeCustomerRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    // =========================================================================
    // Customer
    // =========================================================================

    /**
     * Retourne le StripeCustomer lié au Client.
     * Le crée sur Stripe et le persiste en BDD si inexistant.
     */
    public function resolveCustomer(Client $client): StripeCustomer
    {
        // 1. Déjà en BDD → aucun appel réseau
        $stripeCustomer = $this->stripeCustomerRepository->findByClient($client);

        if ($stripeCustomer instanceof StripeCustomer) {
            return $stripeCustomer;
        }

        // 2. Pas en BDD → on vérifie sur Stripe par email pour éviter les doublons
        $existing = $this->stripeService->findCustomerByEmail($client->getEmail());

        $customerId = $existing !== null
            ? $existing['id']
            : $this->stripeService->createCustomer($client)['id'];

        // 3. Persiste l'entité StripeCustomer
        return $this->saveStripeCustomer($client, $customerId);
    }

    private function saveStripeCustomer(Client $client, string $customerId): StripeCustomer
    {
        $stripeCustomer = new StripeCustomer($client, $customerId);
        $this->entityManager->persist($stripeCustomer);
        $this->entityManager->flush();

        return $stripeCustomer;
    }

    // =========================================================================
    // Payment Methods
    // =========================================================================

    /**
     * Ajoute une nouvelle carte de paiement à un client.
     * Crée le customer Stripe en BDD si c'est sa première carte.
     *
     * @param Client $client          L'utilisateur Symfony
     * @param string $paymentMethodId Le pm_xxx généré par Stripe.js côté front
     * @param bool   $setAsDefault    Définir comme méthode par défaut
     *
     * @return array La méthode de paiement Stripe attachée
     */
    public function addPaymentMethod(
        Client $client,
        string $paymentMethodId,
        bool   $setAsDefault = false
    ): array {
        $stripeCustomer = $this->resolveCustomer($client);

        $paymentMethod = $this->stripeService->attachPaymentMethod(
            $paymentMethodId,
            $stripeCustomer->getCustomerId()
        );

        if ($setAsDefault) {
            $this->stripeService->setDefaultPaymentMethod(
                $stripeCustomer->getCustomerId(),
                $paymentMethodId
            );
        }

        return $paymentMethod;
    }

    /**
     * Récupère les méthodes de paiement d'un client depuis Stripe.
     * Toujours en temps réel — pas de cache BDD.
     */
    public function getPaymentMethods(Client $client, string $type = 'card'): array
    {
        $stripeCustomer = $this->resolveCustomer($client);

        return $this->stripeService->getPaymentMethods(
            $stripeCustomer->getCustomerId(),
            $type
        );
    }

    /**
     * Supprime une méthode de paiement d'un client.
     */
    public function removePaymentMethod(string $paymentMethodId): array
    {
        return $this->stripeService->detachPaymentMethod($paymentMethodId);
    }

    // =========================================================================
    // Payment Intents
    // =========================================================================

    /**
     * Crée un PaymentIntent depuis une liste de produits pour un client.
     * Crée le customer Stripe si nécessaire.
     */
    public function createPaymentIntentFromItems(
        array  $items,
        Client $client,
        string $currency        = 'eur',
        string $paymentMethodId = ''
    ): array {
        $stripeCustomer = $this->resolveCustomer($client);

        return $this->stripeService->createPaymentIntentFromItems(
            $items,
            $stripeCustomer->getCustomerId(),
            $currency,
            $paymentMethodId
        );
    }
}
