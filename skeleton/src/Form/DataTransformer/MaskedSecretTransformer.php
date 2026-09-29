<?php

// src/Form/DataTransformer/MaskedSecretTransformer.php
namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

final class MaskedSecretTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly ?string $originalValue,
        private readonly int $prefixLength = 7,
        private readonly int $suffixLength = 4,
    ) {}

    public function transform($value): string
    {
        if (empty($this->originalValue)) {
            return '';
        }

        $length = mb_strlen($this->originalValue);

        // Sécurité : si la clé est trop courte, on masque tout plutôt que de tout révéler
        if ($length <= $this->prefixLength + $this->suffixLength) {
            return str_repeat('•', 12);
        }

        $prefix = substr($this->originalValue, 0, $this->prefixLength);
        $suffix = substr($this->originalValue, -$this->suffixLength);

        return $prefix . str_repeat('•', 8) . $suffix;
    }

    public function reverseTransform($value): ?string
    {
        if ($value === $this->transform($this->originalValue)) {
            return $this->originalValue;
        }

        return $value ?: $this->originalValue;
    }
}