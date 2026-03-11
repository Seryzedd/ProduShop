<?php

namespace App\Entity\Payment;

use App\Repository\Payment\StripeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StripeRepository::class)]
class Stripe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $authenticationKey = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $publicKey = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $secretKey = '';

    #[ORM\Column]
    private bool $active = false;

    #[ORM\Column]
    private ?float $feesAmount = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthenticationKey(): ?string
    {
        return $this->authenticationKey;
    }

    public function setAuthenticationKey(string $authenticationKey): static
    {
        $this->authenticationKey = $authenticationKey;

        return $this;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): static
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function setSecretKey(string $secretKey): static
    {
        $this->secretKey = $secretKey;

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

    public function getFeesAmount(): ?float
    {
        return $this->feesAmount;
    }

    public function setFeesAmount(float $feesAmount): static
    {
        $this->feesAmount = $feesAmount;

        return $this;
    }
}
