<?php

namespace App\Entity\Utils;

use App\Repository\Utils\selectorRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Utils\SqlGenerator;

#[ORM\Entity(repositoryClass: selectorRepository::class)]
class selector
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column]
    private array $property = [];

    const TYPES = [
        'Entity' => 'entity',
        'Count' => 'count',
        'Sum' => 'sum',
        'Average' => 'average'
    ];

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'selector', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?SqlGenerator $sqlGenerator = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProperty(): array
    {
        return $this->property;
    }

    public function setProperty(array $property): static
    {
        $this->property = $property;

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

    public function getSqlGenerator(): ?SqlGenerator
    {
        return $this->sqlGenerator;
    }

    public function setSqlGenerator(?SqlGenerator $sqlGenerator): static
    {
        $this->sqlGenerator = $sqlGenerator;

        return $this;
    }
}
