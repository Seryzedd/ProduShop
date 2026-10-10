<?php

namespace App\Controller\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Api\StripeService;
use App\Service\Payment\Webhook\StripeWebhookService;
use App\Service\Payment\Webhook\Exception\InvalidWebhookPayloadException;
use App\Service\Payment\Webhook\Exception\InvalidWebhookTokenException;
use App\Service\Order\OrderService;
use Psr\Log\LoggerInterface;

final class WebhookController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    #[Route('/payment/Stripe/webhook', name: 'app_payment_webhook')]
    public function index(Request $request, StripeWebhookService $webhookService): Response
    {
        try {
            $webhookService->handle(
                $request->getContent(),
                $request->headers->get('Stripe-Signature') ?? ''
            );
        } catch (InvalidWebhookPayloadException $e) {
            $this->logger->warning('Stripe webhook rejected: invalid payload or signature.', [
                'reason' => $e->getMessage(),
            ]);
            return new Response('Bad payload', 400);
        } catch (InvalidWebhookTokenException $e) {
            $this->logger->warning('Stripe webhook rejected: invalid token.', [
                'reason' => $e->getMessage(),
            ]);
            return new Response('Invalid token', 400);
        }

        return new Response('Order updated', 200);
    }
}
