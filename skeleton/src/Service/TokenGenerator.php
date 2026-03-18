<?php

namespace App\Service;

class TokenGenerator
{
    private const ALGO      = 'sha256';
    private const TTL       = 300; // 5 minutes de validité

    private string $token = '';

    public function __construct(
        private string $webhookSecret, // injecté depuis %env(WEBHOOK_SECRET)%
    ) {}

    /**
     * Génère un token lié à l'URL et au timestamp actuel.
     */
    public function generate(string $id): string
    {
        $timestamp = time();
        $payload   = $timestamp . '|' . $id;
        $signature = hash_hmac(self::ALGO, $payload, $this->webhookSecret);

        $token = base64_encode($timestamp . '.' . $signature);

        $this->token = $token;

        // Format : timestamp.signature
        return $token;
    }

    public function verify(string $token, string $paymentIntentId): bool
    {
        $decoded = base64_decode($token, strict: true);
        if (!$decoded) {
            return false;
        }

        [$timestamp, $signature] = explode('.', $decoded, 2) + [null, null];
        if (!$timestamp || !$signature) {
            return false;
        }

        $expected = hash_hmac(self::ALGO, $timestamp . '|' . $paymentIntentId, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }
}