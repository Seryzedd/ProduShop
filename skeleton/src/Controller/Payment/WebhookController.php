<?php

namespace App\Controller\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Api\StripeService;

final class WebhookController extends AbstractController
{
    #[Route('/payment/Stripe/webhook', name: 'app_payment_webhook')]
    public function index(Request $request, StripeService $stripeService): Response
    {
        $payload = json_decode($request->getContent(), true);

        if ($payload['type'] === 'payment_intent.succeeded') {
            $piId = $payload['data']['object']['id'];
            $stripeService->distributePayment($piId);
        }

        return new Response('', 200);
    }
}
