<?php

namespace App\Controller\Cart\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Payment\StripeCardType;
use App\Service\Api\StripeService;
use App\Service\Payment\StripeCustomerService;
use App\Service\Cart\CartService;

#[Route('/cart/payment')]
final class PaymentController extends AbstractController
{
    public function __construct(private CartService $cart, private StripeCustomerService $stripeCustomerService, private StripeService $stripeService)
    {

    }

    #[Route('/', name: 'app_cart_payment')]
    public function index(Request $request): Response
    {
        $client = $this->getUser();

        $paymentMethods = $this->stripeCustomerService->getPaymentMethods($client);

        $form = $this->createForm(StripeCardType::class, null, [
            'payment_methods' => $paymentMethods,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('cart/payment/index.html.twig', [
            'form' => $form,
            'stripe_public_key' => $this->stripeService->getPublicKey(),
            'has_saved_methods' => !empty($paymentMethods)
        ]);
    }
}
