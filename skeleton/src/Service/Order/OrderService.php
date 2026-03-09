<?php

namespace App\Service\Order;

use App\Entity\User\AbstractUser;
use App\Entity\User\Order;
use App\Entity\User\OrderItem;
use App\Entity\User\Professional;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    /**
     * Persists two Order entries for a confirmed PaymentIntent:
     * one for the buyer (Client), one for the seller (Professional).
     *
     * @param AbstractUser $buyer
     * @param Professional $seller
     * @param array        $intent  Stripe PaymentIntent array
     * @param array        $items   [['name', 'quantity', 'price', 'package' => Package], ...]
     */
    public function persistFromIntent(
        AbstractUser $buyer,
        Professional $seller,
        array        $intent,
        array        $items
    ): void {
        $paidAt = new \DateTimeImmutable();

        // Order for the buyer
        $buyerOrder = $this->buildOrder($buyer, $intent, $paidAt);
        $this->addItems($buyerOrder, $items);
        $this->em->persist($buyerOrder);

        // Order for the seller
        $sellerOrder = $this->buildOrder($seller, $intent, $paidAt);
        $this->addItems($sellerOrder, $items);
        $this->em->persist($sellerOrder);
    }

    public function save(): void
    {
        $this->em->flush();
    }

    private function buildOrder(AbstractUser $user, array $intent, \DateTimeImmutable $paidAt): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setIntentId($intent['id']);
        $order->setAmount($intent['amount']);
        $order->setCurrency($intent['currency']);
        $order->setStatus($intent['status']);
        $order->setPaidAt($paidAt);

        return $order;
    }

    private function addItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $orderItem = new OrderItem();
            $orderItem->setQuantity($item['quantity']);
            $orderItem->setUnitPrice($item['price']);
            $orderItem->setPackage($item['package']);
            $orderItem->setPurchase($order);

            $this->em->persist($orderItem);
        }
    }
}