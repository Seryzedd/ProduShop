<?php

namespace App\Entity\User\Seo;

use App\Entity\User\Professional;
use App\Repository\User\Seo\SeoProfessionalInformationsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeoProfessionalInformationsRepository::class)]
class SeoProfessionalInformations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string  $meta = '';

    #[ORM\Column(length: 255)]
    private string $title = "";

    #[ORM\Column(length: 255)]
    private string $logoTag = "";

    #[ORM\Column(length: 255)]
    private string $webpageTitle = "";

    #[ORM\OneToOne(inversedBy: 'seoInformations', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Professional $professional = null;

    public function __construct(Professional $professional)
    {
        $this->setProfessional($professional);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMeta(): string
    {
        return $this->meta;
    }

    public function setMeta(string $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getLogoTag(): string
    {
        return $this->logoTag;
    }

    public function setLogoTag(string $logoTag): static
    {
        $this->logoTag = $logoTag;

        return $this;
    }

    public function getWebpageTitle(): string
    {
        return $this->webpageTitle;
    }

    public function setWebpageTitle(string $webpageTitle): static
    {
        $this->webpageTitle = $webpageTitle;

        return $this;
    }

    public function getProfessional(): ?Professional
    {
        return $this->professional;
    }

    public function setProfessional(Professional $professional): static
    {
        $this->professional = $professional;

        return $this;
    }
}
