<?php

namespace App\Controller\Admin\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Payment\StripeType;
use App\Repository\Payment\StripeRepository;
use App\Service\Api\StripeService;

#[Route('/admin/payment/stripe')]
final class StripeController extends AbstractController
{
    public function __construct(private StripeService $stripe)
    {

    }

    #[Route('/', name: 'app_admin_payment_stripe')]
    public function index(Request $request, StripeRepository $stripeRepository, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StripeType::class, $stripeRepository->findStripe());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stripe = $form->getData();

            $entityManager->persist($stripe);
            $entityManager->flush();

            $this->addFlash('success', 'Stripe configuration updated.');
        }

        $error = null;
        try {
            $accounts = $this->stripe->getAccounts();
        } catch (\Exception $e) {
            $accounts = false;
            $error = $e->getMessage();
        }

        return $this->render('admin/payment/stripe/index.html.twig', [
            'form' => $form,
            'connect' => $accounts,
            'error' => $error
        ]);
    }

    #[Route('/payments', name: 'app_admin_payments_stripe')]
    public function getPayments()
    {
        $payments = $this->stripe->getPaymentsIntents();

        return $this->render('admin/payment/stripe/payments.html.twig', [
            'payments' => $payments
        ]);
    }
}
