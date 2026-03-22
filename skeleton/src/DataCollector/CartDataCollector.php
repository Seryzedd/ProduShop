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
        if (!$request->hasSession() || !$request->getSession()->isStarted()) {
            $this->data = [
                'items'                   => [],
                'item_count'              => 0,
                'total_quantity'          => 0,
                'total_price'             => 0.0,
                'has_availability_issues' => false,
            ];
            return;
        }

        $cartItems = $this->cartService->getItems();

        $items = [];
        $totalQuantity = 0;
        $totalPrice = 0.0;
        $hasIssues = false;

        foreach ($cartItems as $item) {
            $pkg = $item->getPackage();

            $quantity  = $item->getQuantity();
            $lineTotal = $item->getLineTotal();

            $totalQuantity += $quantity;
            $totalPrice    += $lineTotal;

            if (!$item->isAvailable()) {
                $hasIssues = true;
            }

            $items[] = [
                'package_id'   => $pkg->getId(),
                'product_name' => $pkg->getProduct()?->getName() ?? 'N/A',
                'quantity'     => $quantity,
                'unit_price'   => $pkg->getFinalPrice(),
                'line_total'   => $lineTotal,
                'stock'        => $pkg->getStock(),
                'available'    => $item->isAvailable(),
                'status'       => $item->getStatus(),
                'added_at'     => $item->getAddedAt()->format('H:i:s'),
            ];
        }

        $this->data = [
            'items'                   => $this->cloneVar($items),
            'item_count'              => count($cartItems),
            'total_quantity'          => $totalQuantity,
            'total_price'             => $totalPrice,
            'has_availability_issues' => $hasIssues,
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