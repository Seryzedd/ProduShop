<?php

namespace App\DTO\Translations;

class TranslationsDTO
{
    public function __construct(private array $languages) {}

    public function getLanguages(): array
    {
        return $this->languages;
    }
}