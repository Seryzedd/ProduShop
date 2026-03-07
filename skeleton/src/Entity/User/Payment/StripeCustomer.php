<?php

namespace App\Entity\User\Payment;

use App\Entity\User\Client;
use App\Repository\User\Payment\StripeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StripeRepository::class)]
class StripeCustomer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $customerId = '';

    #[ORM\OneToOne(inversedBy: 'stripe', cascade: ['persist', 'remove'])]
    private Client $user;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(Client $client, string $customerId)
    {
        $this->user = $client;
        $this->customerId = $customerId;
        $this->createdAt  = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): static
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getUser(): ?Client
    {
        return $this->user;
    }

    public function setUser(Client $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
