<?php

namespace App\Controller\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Api\StripeService;
use App\Service\Order\OrderService;
use Psr\Log\LoggerInterface;
use App\Message\Payment\DistributePaymentMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class WebhookController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route('/payment/Stripe/webhook', name: 'app_payment_webhook')]
    public function index(Request $request, StripeService $stripeService, OrderService $orderService): Response
    {
        $payload = json_decode($request->getContent(), true);
 
        if (!isset($payload['type'], $payload['data']['object'])) {
            return new Response('Bad payload', 400);
        }

        $pi    = $payload['data']['object'];
        $piId  = $pi['id'];

        switch($payload['type']) {
            case 'payment_intent.succeeded':
                $webhookToken = $pi['metadata']['webhook_token'] ?? null;
 
                if (!$webhookToken) {
                    throw new \RuntimeException('Missing webhook token.');
                }

                $transfers = $stripeService->distributePayment($piId, $webhookToken);

                $order = $orderService->getOrderByIntentId($piId);

                $orderService->orderPay($order, $transfers);
                break;
            case 'payment_intent.payment_failed':
                $orderService->updateStatusByIntentId($piId, 'failed');
                break;
            case 'payment_intent.canceled':
                $orderService->updateStatusByIntentId($piId, 'canceled');
                break;
        }

        return new Response('Order updated', 200);
    }
}
