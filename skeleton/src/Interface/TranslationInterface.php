<?php

namespace App\Interface;

interface TranslationInterface
{
    public function getLocale(): string;
    public function setLocale(string $locale): void;
}