<?php

namespace App\Service\Cart;

use App\DTO\Cart\CartItem;
use App\Entity\Product\Package;
use App\Repository\Product\PackageRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    /** @var ?CartItem[] */
    private ?array $items = null;

    public function __construct(
        private RequestStack $requestStack,
        private PackageRepository $packageRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Actions panier
    // -------------------------------------------------------------------------

    public function add(Package $package, int $quantity = 1): void
    {
        $id = $package->getId();

        if (isset($this->items[$id])) {
            $newQty = $this->items[$id]->getQuantity() + $quantity;
            $this->items[$id]->setQuantity($newQty);
        } else {
            $this->items[$id] = new CartItem($package, $quantity);
        }

        $this->save();
    }

    public function remove(int $packageId): void
    {
        unset($this->items[$packageId]);
        $this->save();
    }

    public function updateQuantity(int $packageId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($packageId);
            return;
        }

        if (isset($this->items[$packageId])) {
            $this->items[$packageId]->setQuantity($quantity);
            $this->save();
        }
    }

    public function clear(): void
    {
        $this->items = [];
        $this->save();
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    /** @return CartItem[] */
    public function getItems(): array
    {
        if ($this->items === null) {
            $this->loadFromSession();
        }

        return $this->items;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function getTotalQuantity(): int
    {
        return array_sum(array_map(fn(CartItem $i) => $i->getQuantity(), $this->items));
    }

    public function getTotal(): float
    {
        return array_sum(array_map(fn(CartItem $i) => $i->getLineTotal(), $this->items));
    }

    public function hasAvailabilityIssues(): bool
    {
        foreach ($this->items as $item) {
            if (!$item->isAvailable()) return true;
        }
        return false;
    }

    // -------------------------------------------------------------------------
    // Session : sérialisation / désérialisation
    // -------------------------------------------------------------------------

    private function save(): void
    {
        $data = array_map(fn(CartItem $i) => $i->toArray(), $this->items);
        $this->requestStack->getSession()->set('cart', $data);
    }

    private function loadFromSession(): void
    {
        $data = $this->requestStack->getSession()->get('cart', []);

        foreach ($data as $row) {
            $package = $this->packageRepository->find($row['package_id']);
            if (!$package) continue; // package supprimé entre-temps

            $item = new CartItem($package, $row['quantity']);
            $item->setStatus($row['status']);
            $this->items[$package->getId()] = $item;
        }
    }
}