<?php

namespace App\MessageHandler\Payment;

use App\Message\Payment\DistributePaymentMessage;
use App\Service\Api\StripeService;
use App\Service\Order\OrderService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DistributePaymentHandler
{
    public function __construct(
        private readonly StripeService  $stripeService,
        private readonly OrderService   $orderService,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(DistributePaymentMessage $message): void
    {
        try {
            $transfers = $this->stripeService->distributePayment(
                $message->getPaymentIntentId(),
                $message->getWebhookToken(),
            );

            $order = $this->orderService->getOrderByIntentId(
                $message->getPaymentIntentId()
            );

            foreach ($transfers as $transfer) {
                $transfer->setOrder($order);
            }

            $this->orderService->confirmByIntentId($message->getPaymentIntentId());

        } catch (\Exception $e) {
            $this->logger->error('DistributePayment failed', [
                'piId'    => $message->getPaymentIntentId(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw $e; // ✅ Messenger retentera automatiquement
        }
    }
}