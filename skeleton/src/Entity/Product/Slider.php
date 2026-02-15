<?php

namespace App\Entity\Product;

use App\Repository\Product\SliderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SliderRepository::class)]
class Slider
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    /**
     * @var Collection<int, SlideItem>
     */
    #[ORM\OneToMany(targetEntity: SlideItem::class, mappedBy: 'slider', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $items;

    #[ORM\OneToOne(inversedBy: 'slider', cascade: ['persist', 'remove'])]
    private ?Product $product = null;

    #[ORM\Column]
    private bool $active = false;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, SlideItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(SlideItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setSlider($this);
        }

        return $this;
    }

    public function removeItem(SlideItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getSlider() === $this) {
                $item->setSlider(null);
            }
        }

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

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
