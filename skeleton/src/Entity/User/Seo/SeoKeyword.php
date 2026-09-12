<?php

namespace App\Entity\User\Seo;

use App\Entity\User\AbstractUser;
use App\Repository\User\Seo\SeoKeywordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeoKeywordRepository::class)]
class SeoKeyword
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'seoKeywords', cascade: ['persist', 'remove'])]
    private ?AbstractUser $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getUser(): ?AbstractUser
    {
        return $this->user;
    }

    public function setUser(?AbstractUser $user): static
    {
        $this->user = $user;

        return $this;
    }
}
