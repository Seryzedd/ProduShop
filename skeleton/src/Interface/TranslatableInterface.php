<?php

namespace App\Interface;

interface TranslatableInterface
{
    public function getTranslations(): iterable;
    public function translate(string $locale, string $fallbackLocale = 'fr'): ?TranslationInterface;
    public function addTranslation(TranslationInterface $translation): static;
}