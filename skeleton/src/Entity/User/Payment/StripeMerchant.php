<?php

namespace App\Entity\User\Payment;

use App\Entity\User\Professional;
use App\Repository\User\Payment\StripeMerchantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StripeMerchantRepository::class)]
class StripeMerchant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $accountId = null;

    #[ORM\OneToOne(inversedBy: 'stripeAccount', cascade: ['persist', 'remove'])]
    private ?Professional $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private bool $isReady = false;

    public function __construct(Professional $user, string $accountId)
    {
        $this->user = $user;
        $this->accountId = $accountId;
        $this->createdAt  = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?Professional
    {
        return $this->user;
    }

    public function setUser(?Professional $user): static
    {
        $this->user = $user;

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

    public function isReady(): bool
    {
        return $this->isReady;
    }

    public function setIsReady(bool $isReady): static
    {
        $this->isReady = $isReady;

        return $this;
    }
}
