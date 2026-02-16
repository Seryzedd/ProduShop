<?php

namespace App\DTO\Translations;

class TranslationDTO
{
    public function __construct(
        public string $translationKey,
        public string $translationValue
    ) {
    }

    public function toArray(): array
    {
        return [
            'translationKey' => $this->translationKey,
            'translationValue' => $this->translationValue
        ];
    }

    public function toObject(): object
    {
        return (object) json_encode($this->toArray());
    }

    public function translationFormat()
    {
        return $this->translationKey . ': ' . $this->translationValue;
    }
}