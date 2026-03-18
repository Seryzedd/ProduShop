<?php

namespace App\Message\Payment;

class DistributePaymentMessage
{
    public function __construct(
        private readonly string $paymentIntentId,
        private readonly string $webhookToken,
    ) {}

    public function getPaymentIntentId(): string { return $this->paymentIntentId; }
    public function getWebhookToken(): string { return $this->webhookToken; }
}