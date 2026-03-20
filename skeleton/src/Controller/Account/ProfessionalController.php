<?php

namespace App\Controller\Account;

use App\Service\Payment\StripeMerchantService;
use App\Service\Api\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/account/professional')]
final class ProfessionalController extends AbstractController
{
    public function __construct(
        private readonly StripeMerchantService $stripeMerchantService,
        private readonly StripeService $stripeService,
    ) {}

    /**
     * Generates a Stripe onboarding link and redirects the Professional to it.
     */
    #[Route('/onboarding', name: 'app_professional_stripe_onboarding')]
    public function onboarding(): Response
    {
        /** @var \App\Entity\User\Professional $professional */
        $professional = $this->getUser();

        $merchant = null;
        try {
            $merchant  = $this->stripeMerchantService->resolveAccount($professional);
            $accountId = $merchant->getAccountId();

            if ($merchant && $merchant->isReady()) {
            $this->addFlash('info', 'Stripe account already completed.');
            return $this->redirectToRoute('app_professional_stripe_return');
        }

            $link = $this->stripeService->createAccountLink(
                $accountId,
                $this->generateUrl('app_professional_stripe_refresh', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('app_professional_stripe_return',  [], UrlGeneratorInterface::ABSOLUTE_URL),
            );

            return $this->redirect($link['url']);
        } catch(\Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('app_account_informations');
    }

    /**
     * Called by Stripe after the Professional completes onboarding.
     * Syncs isReady from Stripe API and updates DB.
     */
    #[Route('/return', name: 'app_professional_stripe_return')]
    public function return(): Response
    {
        /** @var \App\Entity\User\Professional $professional */
        $professional = $this->getUser();

        $isReady = $this->stripeMerchantService->refreshIsReady($professional);

        if ($isReady) {
            $this->addFlash('success', 'Account completed.');
        } else {
            $this->addFlash('warning', 'Account not completed. Payments won\'t work.');
        }

        return $this->redirectToRoute('app_account_informations');
    }

    /**
     * Called by Stripe if the onboarding link expired or was abandoned.
     * Generates a fresh link and redirects.
     */
    #[Route('/refresh', name: 'app_professional_stripe_refresh')]
    public function refresh(): Response
    {
        /** @var \App\Entity\User\Professional $professional */
        $professional = $this->getUser();

        $merchant  = $this->stripeMerchantService->resolveAccount($professional);
        $accountId = $merchant->getAccountId();

        $link = $this->stripeService->createAccountLink(
            $accountId,
            $this->generateUrl('app_professional_stripe_refresh', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->generateUrl('app_professional_stripe_return',  [], UrlGeneratorInterface::ABSOLUTE_URL),
        );

        return $this->redirect($link['url']);
    }
}
