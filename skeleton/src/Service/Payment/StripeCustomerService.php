<?php

namespace App\Service\Payment;

use App\Entity\User\Payment\StripeCustomer;
use App\Entity\User\Client;
use App\Repository\User\Payment\StripeRepository as StripeCustomerRepository;
use App\Service\Api\StripeService;
use App\Service\Payment\StripeMerchantService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Cart\CartService;

/**
 * Responsability : Link Customer and update it with Doctrine to API Stripe Service
 */
class StripeCustomerService
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly StripeCustomerRepository $stripeCustomerRepository,
        private readonly StripeMerchantService $stripeMerchantService,
        private readonly EntityManagerInterface $entityManager,
        private CartService $cart
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
    // Payment Intents — multi-vendor
    // =========================================================================

    /**
     * Creates one PaymentIntent per Professional found in the cart.
     * If a merchant account is not ready, that group is skipped and added to $failures.
     *
     * Returns:
     * [
     *   'succeeded' => [
     *     ['intent' => [...], 'merchant' => 'acct_xxx', 'items' => [...]], ...
     *   ],
     *   'failed' => [
     *     ['merchant' => 'CompanyName', 'reason' => '...', 'items' => [...]], ...
     *   ],
     * ]
     */
    public function createPaymentIntentsFromCart(
        Client  $client,
        string  $currency        = 'eur',
        ?string $paymentMethodId = null
    ): array {
        $stripeCustomer = $this->resolveCustomer($client);
        $grouped        = $this->getCartItemsGroupedByProfessional();

        $succeeded = [];
        $failed    = [];

        foreach ($grouped as $professionalId => $group) {
            $professional = $group['professional'];
            $items        = $group['items'];

            $merchant  = $this->stripeMerchantService->resolveAccount($professional);
            try {
                
                $accountId = $merchant->getAccountId();

                if ($accountId === null) {
                    throw new \RuntimeException(sprintf(
                        'No Stripe Connect account found for "%s".',
                        $professional->getCompanyName()
                    ));
                }

                // Check if account is iban etc is complete with iban etc
                // If the account is not ready yet, funds are held on the platform.
                // Stripe will automatically transfer them once onboarding is complete.
                $resolvedAccountId = $this->stripeService->isConnectAccountReady($accountId)
                    ? $accountId
                    : null;

                $intent = $this->stripeService->createPaymentIntentFromItems(
                    $items,
                    $stripeCustomer->getCustomerId(),
                    $currency,
                    $paymentMethodId,
                    $resolvedAccountId
                );

                $succeeded[] = [
                    'intent'   => $intent,
                    'merchant' => $accountId,
                    'professional' => $professional,
                    'items'    => $items,
                ];

                $this->stripeMerchantService->updateStocks($items);

            } catch (\Exception $e) {
                $failed[] = [
                    'merchant' => $professional->getCompanyName(),
                    'reason'   => $e->getMessage(),
                    'items'    => $items,
                ];
            }
        }

        return ['succeeded' => $succeeded, 'failed' => $failed];
    }

    /**
     * Converts cart items into the array format expected by StripeService,
     * grouped by Professional.
     *
     * Returns:
     * [
     *   $professionalId => [
     *     'professional' => Professional,
     *     'items'        => [['name', 'quantity', 'price' (float € unit TTC)], ...]
     *   ],
     *   ...
     * ]
     */
    public function getCartItemsGroupedByProfessional(): array
    {
        $grouped = [];

        foreach ($this->cart->getItems() as $item) {
            $package      = $item->getPackage();
            $professional = $package->getProduct()->getCompany();
            $id           = $professional->getId();

            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'professional' => $professional,
                    'items'        => [],
                ];
            }

            $grouped[$id]['items'][] = [
                'name'     => $package->getProduct()->getName() . ' ' . $package->getName(),
                'quantity' => $item->getQuantity(),
                'price'    => $package->getFinalPrice(), // unit price TTC in euros (float)
                'package'  => $package,
                'tax' => $package->getTaxe() *100
            ];
        }

        return $grouped;
    }

    /**
     * Flat list of cart items (all professionals merged).
     * Used for the success page summary.
     *
     * @return array [['name', 'quantity', 'price'], ...]
     */
    public function getCartItems(): array
    {
        $items = [];

        foreach ($this->getCartItemsGroupedByProfessional() as $group) {
            foreach ($group['items'] as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
