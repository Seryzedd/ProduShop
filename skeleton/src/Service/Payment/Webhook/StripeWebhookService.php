<?php

namespace App\Service\Payment\Webhook;

use App\Service\Order\OrderService;
use App\Service\Api\StripeService;
use App\Service\Payment\Webhook\WebhookTokenValidator;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Psr\Log\LoggerInterface;
use App\Service\Payment\Webhook\Exception\InvalidWebhookPayloadException;
use App\Repository\Payment\StripeRepository;

final class StripeWebhookService
{
    public function __construct(
        private readonly WebhookTokenValidator $tokenValidator,
        private readonly StripeService $stripeService,
        private readonly OrderService $orderService,
        private readonly LoggerInterface $logger,
        private readonly StripeRepository $stripeConfig,
    ) {}

    public function handle(string $rawPayload, string $signatureHeader): void
    {
        $config = $this->stripeConfig->findStripe();

        if(!$config) {
            throw new InvalidWebhookPayloadException('No active Stripe\'s configuration found.');
        }

        $this->stripe = $config;

        try {
            $event = Webhook::constructEvent(
                $rawPayload,
                $signatureHeader,
                $config->getWebhookSecret()
            );
        } catch (\UnexpectedValueException|SignatureVerificationException $e) {

            throw new InvalidWebhookPayloadException('Invalid Stripe webhook signature: ' . $e->getMessage());
        }

        $pi   = $event->data->object;
        $piId = $pi->id;

        match ($event->type) {
            'payment_intent.succeeded'      => $this->handleSucceeded($pi, $piId),
            'payment_intent.payment_failed' => $this->orderService->updateStatusByIntentId($piId, 'failed'),
            'payment_intent.canceled'       => $this->orderService->updateStatusByIntentId($piId, 'canceled'),
            default                         => $this->logger->info('Unhandled Stripe event type', ['type' => $event->type]),
        };
    }

    private function handleSucceeded(object $pi, string $piId): void
    {
        $this->tokenValidator->validate($pi);

        $order = $this->orderService->getOrderByIntentId($piId);
        $this->orderService->orderPay($order);

        // Stripe's fees returned by Stripe
        $stripeFee = $this->resolveStripeFeeAmount($piId);
        $order->setStripeFeesAmount($stripeFee);

        $this->orderService->save();
    }

    private function resolveStripeFeeAmount(string $piId): int
    {
        $pi = $this->stripeService->getPaymentIntent($piId); // expand latest_charge.balance_transaction nécessaire

        return $pi['latest_charge']['balance_transaction']['fee'] ?? 0;
    }
}