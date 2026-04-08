<?php

namespace App\Trait;

use App\Interface\TranslationInterface;
use Doctrine\Common\Collections\Collection;

trait TranslatableTrait
{
    private Collection $translations;

    public function initTranslations(): void
    {
        $this->translations = new ArrayCollection();
    }

    public function translate(string $locale, string $fallbackLocale = 'fr'): ?TranslationInterface
    {
        // Cherche la traduction demandée
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(TranslationInterface $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setTranslatable($this);
        }

        return $this;
    }
    
    public function removeTranslation(TranslationInterface $translation): void
    {
        if ($this->translations->contains($translation)) {
            $this->translations->removeElement($translation);
        }
    }

    public function hasTranslation(string $locale): bool
    {
        $filtered = $this->translations->filter(static fn (TranslationInterface $translation) => $translation->getLocale() === $locale);

        return !$filtered->isEmpty();
    }
}