<?php

namespace App\Entity\Utils;

use App\Entity\User\AbstractUser;
use App\Repository\Utils\SqlGeneratorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User\Payment\Payment;
use App\Entity\Product;
use App\Entity\User\Order;
use App\Entity\User\OrderItem;

#[ORM\Entity(repositoryClass: SqlGeneratorRepository::class)]
class SqlGenerator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, condition>
     */
    #[ORM\OneToMany(targetEntity: condition::class, mappedBy: 'extractor', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $conditions;

    const CLASSELIST = [
        'Payment' => Payment::class,
        'Product' => Product\Product::class,
        'Package' => Product\Package::class,
        'Shelf' => Product\Shelf::class,
        'Order' => Order::class,
        'OrderItem' => OrderItem::class
    ];

    #[ORM\Column(length: 255)]
    private ?string $entityclassName = null;

    /**
     * @var Collection<int, selector>
     */
    #[ORM\OneToMany(targetEntity: selector::class, mappedBy: 'sqlGenerator', orphanRemoval: true, cascade: ['persist'])]
    private Collection $selector;

    #[ORM\ManyToOne(inversedBy: 'sqlGenerators')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AbstractUser $user = null;

    public function __construct(AbstractUser $user)
    {
        $this->conditions = new ArrayCollection();
        $this->selector = new ArrayCollection();
        $this->user = $user;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, condition>
     */
    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(condition $condition): static
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions->add($condition);
            $condition->setExtractor($this);
        }

        return $this;
    }

    public function removeCondition(condition $condition): static
    {
        if ($this->conditions->removeElement($condition)) {
            // set the owning side to null (unless already changed)
            if ($condition->getExtractor() === $this) {
                $condition->setExtractor(null);
            }
        }

        return $this;
    }

    public function getEntityclassName(): ?string
    {
        if($this->entityclassName === null) {
            return array_key_first($this::CLASSELIST);
        }

        return $this->entityclassName;
    }

    public static function getClassNamespace(string $classname): string
    {
        return self::CLASSELIST[$classname];
    }

    public function setEntityclassName(string $entityclassName): static
    {
        $this->entityclassName = $entityclassName;

        return $this;
    }

    /**
     * @return Collection<int, selector>
     */
    public function getSelector(): Collection
    {
        return $this->selector;
    }

    public function addSelector(selector $selector): static
    {
        if (!$this->selector->contains($selector)) {
            $this->selector->add($selector);
            $selector->setSqlGenerator($this);
        }

        return $this;
    }

    public function removeSelector(selector $selector): static
    {
        if ($this->selector->removeElement($selector)) {
            // set the owning side to null (unless already changed)
            if ($selector->getSqlGenerator() === $this) {
                $selector->setSqlGenerator(null);
            }
        }

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

    public static function getEntityclassNames()
    {
        $result = [];
        foreach(self::CLASSELIST as $key => $value) {
            $result[$key] = $key;
        }

        return $result;
    }
}
