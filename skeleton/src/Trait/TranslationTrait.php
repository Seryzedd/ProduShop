<?php

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use App\Interface\TranslationInterface;

trait TranslationTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 5)]
    private string $locale;

    public function getId(): ?int { return $this->id; }

    public function getLocale(): string {return $this->locale;}
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}