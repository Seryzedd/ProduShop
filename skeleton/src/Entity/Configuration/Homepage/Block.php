<?php

namespace App\Entity\Configuration\Homepage;

use App\Repository\Configuration\Homepage\BlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Picture;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
class Block
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Picture $backgroundImage = null;

    #[ORM\Column(nullable: true, type: Types::TEXT)]
    private ?string $backgroundColor = null;

    #[ORM\Column(length: 50, nullable: false)]
    private string $textColor = '#000';

    #[ORM\Column]
    private int $position = 0;

    public const TYPE = [
        'text' => 'text'
    ];

    #[ORM\Column(length: 10)]
    private ?string $type = 'text';

    #[ORM\Column]
    private bool $active = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): static
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getTextColor(): string
    {
        return $this->textColor;
    }

    public function setTextColor(string $color): static
    {
        $this->textColor = $color;

        return $this;
    }

    public function setBackgroundImage(Picture $backgroundImage): static
    {
        $this->backgroundImage = $backgroundImage;

        return $this;
    }

    public function getBackgroundImage(): ?Picture
    {
        return $this->backgroundImage;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }
}
