<?php

namespace App\Service\Payment;

use App\Entity\User\Payment\StripeMerchant;
use App\Entity\User\Professional;
use App\Entity\Product\Package;
use App\Repository\User\Payment\StripeMerchantRepository;
use App\Service\Api\StripeService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Responsability : Link Professional to a Stripe Connect account (acct_xxx)
 * and persist it in DB. Mirrors StripeCustomerService for the seller side.
 */
class StripeMerchantService
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly StripeMerchantRepository $stripeMerchantRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    // =========================================================================
    // Connect Account
    // =========================================================================

    /**
     * Return StripeMerchant of Professional.
     * Create it if does not exist.
     */
    public function resolveAccount(Professional $professional): StripeMerchant
    {
        // 1. in BDD → no api request
        $stripeMerchant = $this->stripeMerchantRepository->findByProfessional($professional);

        if ($stripeMerchant instanceof StripeMerchant) {
            return $stripeMerchant;
        }

        // 2. No in BDD → send Stripe request
        $account = $this->stripeService->createConnectAccount($professional);

        return $this->saveStripeMerchant($professional, $account['id']);
    }

    /**
     * Return l'accountId (acct_xxx) of Professional, or null.
     */
    public function getAccountId(Professional $professional): ?string
    {
        $stripeMerchant = $this->stripeMerchantRepository->findByProfessional($professional);

        return $stripeMerchant?->getAccountId();
    }

    /**
     * Remove Connect account from BDD (Called by webhook account deleted).
     */
    public function removeAccount(Professional $professional): void
    {
        $stripeMerchant = $this->stripeMerchantRepository->findByProfessional($professional);

        if ($stripeMerchant === null) {
            return;
        }

        $this->entityManager->remove($stripeMerchant);
        $this->entityManager->flush();
    }

    /**
     * Syncs isReady from Stripe API and persists it.
     * Called on the onboarding return URL.
     */
    public function refreshIsReady(Professional $professional): bool
    {
        $stripeMerchant = $this->stripeMerchantRepository->findByProfessional($professional);

        if ($stripeMerchant === null) {
            return false;
        }

        $isReady = $this->stripeService->isConnectAccountReady($stripeMerchant->getAccountId());
        $stripeMerchant->setIsReady($isReady);
        $this->entityManager->flush();

        return $isReady;
    }

    public function updatePackageStock(Package $package, string $quantity)
    {
        $package->setStock(max(0, $package->getStock() - $quantity));

        $this->entityManager->persist($package);

        return $package;
    }

    public function updateStocks(array $items)
    {
        foreach ($items as $item) {
            $this->updatePackageStock($item['package'], $item['quantity']);
        }
    }


    // =========================================================================
    // Private
    // =========================================================================

    private function saveStripeMerchant(Professional $professional, string $accountId): StripeMerchant
    {
        $stripeMerchant = new StripeMerchant($professional, $accountId);
        $stripeMerchant->setIsReady($this->stripeService->isConnectAccountReady($accountId));

        $this->entityManager->persist($stripeMerchant);
        $this->entityManager->flush();

        return $stripeMerchant;
    }

}