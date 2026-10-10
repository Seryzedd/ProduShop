<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

class TokenGenerator
{
    private const ALGO      = 'sha256';
    private const TTL       = 259200; // 72h validity

    private string $token = '';

    public function __construct(
        private string $paymentSecret,
        private CacheInterface $cache,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Génère un token lié à l'URL et au timestamp actuel.
     */
    public function generate(string $id): string
    {
        $token = $this->encodeBase64($this->hash($id), time());

        $this->cache->get(
            $this->cacheKey($id),
            function (ItemInterface $item) {
                $item->expiresAfter(self::TTL);
                return true;
            }
        );

        // Format : timestamp.signature
        return $token;
    }

    public function verify(string $token, string $paymentIntentId): bool
    {
        [$timestamp, $signature] = $this->decodeBase64($token);

        if (!$timestamp || !$signature) {
            return false;
        }

        if ((time() - (int) $timestamp) > self::TTL) {
            $this->logger->warning('verify: expired', ['age' => time() - (int) $timestamp]);
            return false;
        }

        $expected = $this->hash($paymentIntentId);

        dump($signature, $expected);

        if (!hash_equals($expected, $signature)) {
            $this->logger->warning('verify: hmac mismatch');
            return false;
        }

        return hash_equals($expected, $signature);
    }

    private function consume(string $id): bool
    {
        $key = $this->cacheKey($id);

        /** return false if never generated or already consumed */
        if (!$this->cache->getItem($key)->isHit()) {
            return false;
        }

        /** if key exist consume it immediately */
        $this->cache->delete($key);

        return true;
    }

    private function cacheKey(string $id): string
    {
        return 'payment_token_' . hash('sha256', $id); // évite tout caractère invalide dans la clé cache
    }

    private function hash(string $id)
    {
        return hash_hmac(self::ALGO, $id, $this->paymentSecret);
    }

    private function encodeBase64(string $signature, int $timestamp): string
    {
        return base64_encode($timestamp . '|' . $signature);
    }

    private function decodeBase64(string $code): array
    {
        $decoded = base64_decode($code, strict: true);

        if(!$decoded) {
            throw new \Exception('Token decode error');
        }

        return explode('|', $decoded, 2);
    }


}