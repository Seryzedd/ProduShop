<?php

namespace App\Service\Payment\Webhook;

use App\Service\Order\OrderService;
use App\Service\Api\StripeService;
use App\Repository\Payment\StripeRepository;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Entity\Payment\Stripe;
use Psr\Log\LoggerInterface;
use App\Service\Payment\Webhook\Exception\InvalidWebhookPayloadException;
use App\Service\Payment\Webhook\Exception\InvalidWebhookTokenException;
use App\Service\TokenGenerator;

final class WebhookTokenValidator
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly OrderService $orderService,
        private readonly LoggerInterface $logger,
        private readonly TokenGenerator $tokenGenerator
    ) {}

    public function config(Stripe $stripe): self
    {
        $this->stripe = $stripe;
        return $this;
    }

    public function handle(string $rawPayload, string $signatureHeader): void
    {
        $config = $this->stripe;

        if(!$config) {
            throw new InvalidWebhookPayloadException('No active Stripe\'s configuration found.');
        }
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
        $order = $this->orderService->getOrderByIntentId($piId);

        if ($order === null) {
            // Jamais vu ce PaymentIntent — rien à traiter, mais pas une erreur de sécurité
            $this->logger->warning('Webhook received for unknown PaymentIntent.', ['intent' => $piId]);
            return;
        }

        // ✅ Idempotence métier — retry Stripe légitime, pas une attaque
        if ($order->getStatus() === 'paid') {
            $this->logger->info('Payment already processed, skipping.', ['intent' => $piId]);
            return;
        }

        // ✅ Vérification du token — seulement pour un traitement réellement nouveau
        $this->tokenValidator->validate($pi);

        $this->orderService->orderPay($order);

        $stripeFee = $this->resolveStripeFeeAmount($piId);
        $order->getPayment()->setStripeFeeAmount($stripeFee);

        $this->orderService->save();
    }

    private function resolveStripeFeeAmount(string $piId): int
    {
        $pi = $this->stripeService->getPaymentIntent($piId);

        return $pi['latest_charge']['balance_transaction']['fee'] ?? 0;
    }

    /**
     * @param object $paymentIntent Objet Stripe (stdClass/StripeObject) issu de Event::data->object
     *
     * @throws InvalidWebhookTokenException si le token est absent, expiré, invalide ou déjà consommé
     */
    public function validate(object $paymentIntent): void
    {
        $token    = $paymentIntent->metadata->webhook_token ?? null;
        $paymentId = $paymentIntent->id ?? null;

        dump($token, $paymentId);

        if (!$token || !$paymentId) {
            throw new InvalidWebhookTokenException('Missing webhook token or paymentId.');
        }

        if (!$this->tokenGenerator->verify($token, $paymentId)) {
            throw new InvalidWebhookTokenException(sprintf(
                'Invalid or expired webhook token for order ref "%s".',
                $paymentId
            ));
        }
    }
}