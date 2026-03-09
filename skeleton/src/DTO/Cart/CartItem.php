<?php

namespace App\DTO\Cart;

use App\Entity\Product\Package;

class CartItem
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    public const MIN_QUANTITY = 1;
    public const MAX_QUANTITY = 99;

    private Package $package;
    private int $quantity;
    private string $status;
    private \DateTime $addedAt;

    public function __construct(Package $package, int $quantity = self::MIN_QUANTITY)
    {
        $this->package  = $package;
        $this->quantity = max(self::MIN_QUANTITY, min($quantity, self::MAX_QUANTITY));
        $this->status   = self::STATUS_PENDING;
        $this->addedAt  = new \DateTime();
    }

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = max(self::MIN_QUANTITY, min($quantity, self::MAX_QUANTITY));
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELLED])) {
            throw new \InvalidArgumentException("Statut invalide : $status");
        }
        $this->status = $status;
        return $this;
    }

    public function getAddedAt(): \DateTime
    {
        return $this->addedAt;
    }

    /** Prix total de la ligne (prix TTC * quantité) */
    public function getLineTotal(): float
    {
        dump($this);
        return $this->package->getFinalPrice() * $this->quantity;
    }

    public function getFinalPriceCents(): int
    {
        return round($this->getLineTotal() * 100);
    }

    /** Vérifie si la quantité demandée est disponible en stock */
    public function isAvailable(): bool
    {
        return $this->package->getStock() >= $this->quantity;
    }

    /** Sérialisation pour la session (on ne stocke pas l'objet Doctrine directement) */
    public function toArray(): array
    {
        return [
            'package_id' => $this->package->getId(),
            'quantity'   => $this->quantity,
            'status'     => $this->status,
            'added_at'   => $this->addedAt->format('Y-m-d H:i:s'),
        ];
    }
}