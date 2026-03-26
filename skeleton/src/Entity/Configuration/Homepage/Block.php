<?php

namespace App\Entity\Configuration\Homepage;

use App\Entity\Configuration\AbstractText;
use App\Repository\Configuration\Homepage\BlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Picture;
use App\Entity\Configuration\Paragraph;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
class Block
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Picture $backgroundImage = null;

    #[ORM\Column(nullable: true, type: Types::TEXT)]
    private ?string $backgroundColor = null;

    #[ORM\Column]
    private int $position = 0;

    public const TYPE = [
        'text' => 'text'
    ];

    #[ORM\Column(length: 10)]
    private ?string $type = 'text';

    #[ORM\Column]
    private bool $active = false;

    #[ORM\OneToMany(targetEntity: AbstractText::class, mappedBy: 'block', cascade: ['persist', 'remove'])]
    private Collection $htmlElement;

    public function __construct()
    {
        $this->htmlElement = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getHtmlElement(): Collection
    {
        return $this->htmlElement;
    }

    public function addHtmlElement(AbstractText $element): static  // ✅ gestion Collection
    {
        if (!$this->htmlElement->contains($element)) {
            $this->htmlElement->add($element);
            $element->setBlock($this);
        }
        return $this;
    }

    public function removeHtmlElement(AbstractText $element): static
    {
        if ($this->htmlElement->removeElement($element)) {
            if ($element->getBlock() === $this) {
                $element->setBlock(null);
            }
        }
        return $this;
    }
}
