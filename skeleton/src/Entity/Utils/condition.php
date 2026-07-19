<?php

namespace App\Entity\Utils;

use App\Repository\Utils\conditionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: conditionRepository::class)]
#[ORM\Table(name: '`condition`')]
class condition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    const OPERATORS = [
        '<' => "<",
        '>' => '>',
        "<=" => '<=',
        '>=' => '>=',
        '=' => '=',
        '!=' => '!=',
        'Like' => 'LIKE',
        'In' => 'IN'

    ];

    #[ORM\Column(length: 10)]
    private ?string $operator = null;

    #[ORM\Column(length: 255)]
    private ?string $field = null;

    #[ORM\Column(length: 100)]
    private ?string $alias = null;

    #[ORM\ManyToOne(inversedBy: 'conditions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SqlGenerator $extractor = null;

    #[ORM\Column(length: 50)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    public function getExtractor(): ?SqlGenerator
    {
        return $this->extractor;
    }

    public function setExtractor(?SqlGenerator $extractor): static
    {
        $this->extractor = $extractor;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
