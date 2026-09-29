<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class EncryptedType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) return null;

        $key = sodium_hex2bin($_ENV['APP_ENCRYPTION_KEY']);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($value, $nonce, $key);

        return base64_encode($nonce . $cipher);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) return null;

        $key = sodium_hex2bin($_ENV['APP_ENCRYPTION_KEY']);
        $decoded = base64_decode($value);
        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        return sodium_crypto_secretbox_open($cipher, $nonce, $key) ?: null;
    }

    public function getName(): string
    {
        return 'encrypted';
    }
}