<?php

namespace App\Entity\Translations;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Product\Shelf;
use Doctrine\DBAL\Types\Types;
use App\Interface\TranslationInterface;
use App\Trait\TranslationTrait;
use App\Entity\User\Professional;

#[ORM\Entity]
class ProfessionalTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Column(length: 255, type: Types::TEXT)]
    private string $description = '';

    #[ORM\ManyToOne(inversedBy: 'translations')]
    private Professional $translatable;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getTranslatable(): Professional { return $this->translatable; }
    public function setTranslatable(object $translatable): void
    {
        $this->translatable = $translatable;

        $translatable->addTranslation($this);
    }
}