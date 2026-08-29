<?php

namespace App\Entity\Helper;

use App\Repository\Helper\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Documentation>
     */
    #[ORM\OneToMany(targetEntity: Documentation::class, mappedBy: 'category', orphanRemoval: true)]
    private Collection $documentations;

    public function __construct()
    {
        $this->documentations = new ArrayCollection();
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
     * @return Collection<int, Documentation>
     */
    public function getDocumentations(): Collection
    {
        return $this->documentations;
    }

    public function addDocumentation(Documentation $documentation): static
    {
        if (!$this->documentations->contains($documentation)) {
            $this->documentations->add($documentation);
            $documentation->setCategory($this);
        }

        return $this;
    }

    public function removeDocumentation(Documentation $documentation): static
    {
        if ($this->documentations->removeElement($documentation)) {
            // set the owning side to null (unless already changed)
            if ($documentation->getCategory() === $this) {
                $documentation->setCategory(null);
            }
        }

        return $this;
    }

    public function getActiveDocumentations()
    {
        $filter = $this->documentations->filter(static fn (Documentation $documentation) => $documentation->isActive() === true);

        return $filter;
    }

    public function hasActiveDocumentation(): bool
    {
        return count($this->getActiveDocumentations()) > 0;
    }
}
