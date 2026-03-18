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
 
        match ($payload['type']) {
 
            'payment_intent.succeeded' => (function() use ($pi, $piId, $stripeService, $orderService) {
                $webhookToken = $pi['metadata']['webhook_token'] ?? null;
 
                if (!$webhookToken) {
                    throw new \RuntimeException('Missing webhook token.');
                }

                $this->bus->dispatch(new DistributePaymentMessage($piId, $webhookToken));

                $transfers = $stripeService->distributePayment($piId, $webhookToken);

                $order = $orderService->getOrderByIntentId($piId);

                $orderService->orderPay($order, $transfers);
                //try{
                //    // Distribuer les paiements aux marchands
                //    
                //} catch(\Exception $exception) {
                //    $this->logger->error('Webhook distributePayment failed', [
                //        'piId'      => $piId,
                //        'message'   => $exception->getMessage(),
                //        'trace'     => $exception->getTraceAsString(),
                //    ]);
                //}
                
            })(),
 
            'payment_intent.payment_failed' => (function() use ($piId, $orderService) {
                $orderService->updateStatusByIntentId($piId, 'failed');
            })(),
 
            'payment_intent.canceled' => (function() use ($piId, $orderService) {
                $orderService->updateStatusByIntentId($piId, 'canceled');
            })(),
 
            default => null,
        };
 
        return new Response('', 200);
    }
}
