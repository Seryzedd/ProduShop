<?php

namespace App\Entity\User\Payment;

use App\Entity\User\Order;
use App\Repository\User\Payment\TransferRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferRepository::class)]
class Transfer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $transferId = null;

    #[ORM\Column(length: 255)]
    private ?string $chargeId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(length: 10)]
    private ?string $currency = null;

    #[ORM\ManyToOne(inversedBy: 'transfers')]
    private ?Order $orderClass = null;

    #[ORM\Column(length: 255)]
    private ?string $accountId = null;

    public function __construct(
        string $transferId,
        string $chargeId,
        string $merchantAccountId,
        int $amount,
        string $currency = 'eur',
    ) {
        $this->transferId = $transferId;
        $this->chargeId = $chargeId;
        $this->accountId = $merchantAccountId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransferId(): ?int
    {
        return $this->transferId;
    }

    public function setTransferId(int $transferId): static
    {
        $this->transferId = $transferId;

        return $this;
    }

    public function getChargeId(): ?int
    {
        return $this->chargeId;
    }

    public function setChargeId(int $chargeId): static
    {
        $this->chargeId = $chargeId;

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

    public function getOrderClass(): ?Order
    {
        return $this->orderClass;
    }

    public function setOrderClass(?Order $orderClass): static
    {
        $this->orderClass = $orderClass;

        return $this;
    }

    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId): static
    {
        $this->accountId = $accountId;

        return $this;
    }
}
