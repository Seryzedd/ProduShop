<?php

namespace App\Entity\User;

use App\Entity\User\Payment\Payment;
use App\Entity\User\Payment\Transfer;
use App\Repository\User\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\User\Client;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $intentId = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(length: 15)]
    private ?string $currency = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'purchase', orphanRemoval: true)]
    private Collection $orderItems;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'orders', cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Professional $merchant = null;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntentId(): ?string
    {
        return $this->intentId;
    }

    public function setIntentId(string $intentId): static
    {
        $this->intentId = $intentId;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setPurchase($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getPurchase() === $this) {
                $orderItem->setPurchase(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getItemsByMerchant()
    {
        $items = [];

        foreach ($this->orderItems as $item) {
            $price = isset($items[$item->getMerchant()->getId()]) ? $items[$item->getMerchant()->getId()]['totalAmount'] : 0;
            
            if(!isset($items[$item->getMerchant()->getId()])) {
                $items[$item->getMerchant()->getId()] = [
                    'totalAmount' => 0,
                    'items' => [],
                    'merchant' => $item->getMerchant(),
                ];
            }

            $items[$item->getMerchant()->getId()] = [
                'totalAmount' => $item->getUnitPrice() + $price,
                'merchant' => $item->getMerchant(),
                'items' => array_merge($items[$item->getMerchant()->getId()]['items'], [$item])
            ];
        }

        return $items;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getMerchant(): ?Professional
    {
        return $this->merchant;
    }

    public function setMerchant(?Professional $merchant): static
    {
        $this->merchant = $merchant;

        return $this;
    }
}
