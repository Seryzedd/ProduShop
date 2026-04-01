<?php

namespace App\Entity\Translations;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Product\Shelf;
use Doctrine\DBAL\Types\Types;
use App\Interface\TranslationInterface;

#[ORM\Entity]
class ShelfTranslation implements TranslationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    private Shelf $translatable;

    #[ORM\Column(length: 5)]
    private string $locale;

    // Nullable : une traduction peut n'exister que partiellement
    #[ORM\Column(length: 255)]
    private string $name = '';

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): void { $this->name = $name; }

    public function getLocale(): string {return $this->locale;}
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getTranslatable(): Shelf { return $this->translatable; }
    public function setTranslatable(object $translatable): void
    {
        $this->translatable = $translatable;

        $translatable->addTranslation($this);
    }
}