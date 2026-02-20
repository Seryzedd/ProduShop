<?php

namespace App\DataCollector;

use App\Cart\CartItem;
use App\Service\Cart\CartService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class CartDataCollector extends DataCollector
{
    public function __construct(private CartService $cartService) {}

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $items = [];

        foreach ($this->cartService->getItems() as $item) {
            $pkg = $item->getPackage();
            $items[] = [
                'package_id'   => $pkg->getId(),
                'product_name' => $pkg->getProduct()?->getName() ?? 'N/A',
                'quantity'     => $item->getQuantity(),
                'unit_price'   => $pkg->getFinalPrice(),
                'line_total'   => $item->getLineTotal(),
                'stock'        => $pkg->getStock(),
                'available'    => $item->isAvailable(),
                'status'       => $item->getStatus(),
                'added_at'     => $item->getAddedAt()->format('H:i:s'),
            ];
        }

        $this->data = [
            'items'             => $items,
            'item_count'        => $this->cartService->getItemCount(),
            'total_quantity'    => $this->cartService->getTotalQuantity(),
            'total_price'       => $this->cartService->getTotal(),
            'has_availability_issues' => $this->cartService->hasAvailabilityIssues(),
        ];
    }

    public function getItems(): array                 { return $this->data['items'] ?? []; }
    public function getItemCount(): int               { return $this->data['item_count'] ?? 0; }
    public function getTotalQuantity(): int           { return $this->data['total_quantity'] ?? 0; }
    public function getTotalPrice(): float            { return $this->data['total_price'] ?? 0.0; }
    public function hasAvailabilityIssues(): bool     { return $this->data['has_availability_issues'] ?? false; }

    public function getName(): string  { return 'app.cart_collector'; }
    public function reset(): void      { $this->data = []; }
}