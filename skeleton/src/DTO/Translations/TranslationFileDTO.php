<?php

namespace App\DTO\Translations;

use App\DTO\Translations\TranslationDTO;

class TranslationFileDTO
{
    private string $provider;
    private string $locale;
    private string $format;
    private array $translations;

    public function __construct(
        string $filename
    ) {
        $filenameParts = explode('.', $filename);

        $this->provider = $filenameParts[0];
        $this->locale = $filenameParts[1];
        $this->format = $filenameParts[2];

        $this->translations = [];
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function addTranslation(TranslationDTO $translation): void
    {
        $this->translations[] = $translation;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getTranslationsToArray(): array
    {
        $translations = [];
        foreach($this->translations as $translation) {
            $translations[$translation->getTranslationKey()] = $translation->getTranslationValue();
        }

        return $translations;
    }

    public function removeTranslation(TranslationDTO $translation): void
    {
        $this->translations = array_filter($this->translations, function ($t) use ($translation) {
            return $t !== $translation;
        });
    }

    public function getFilename(): string
    {
        return $this->provider . '.' . $this->locale . '.' . $this->format;
    }
}