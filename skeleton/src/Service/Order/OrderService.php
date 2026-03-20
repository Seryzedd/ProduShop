<?php

namespace App\Service\Order;

use App\Entity\User\AbstractUser;
use App\Entity\User\Order;
use App\Entity\User\OrderItem;
use App\Entity\User\Professional;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\User\OrderRepository;
use App\Entity\User\Payment\Payment;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrderRepository        $orderRepository,
        ) {}

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
        Payment $payment,
        AbstractUser $buyer,
        Professional $seller,
        array        $intent,
        array        $items
    ): void {
        $paidAt = new \DateTimeImmutable();

        // Order for the buyer
        $buyerOrder = $this->buildOrder($seller, $intent, $paidAt);
        $this->addItems($buyerOrder, $items, $seller);
        $payment->addOrder($buyerOrder);
        $payment->setAmount($buyerOrder->getAmount() + $payment->getAmount());

        $this->em->persist($payment);
        $this->em->persist($buyerOrder);
    }

    public function save(): void
    {
        $this->em->flush();
    }

    private function buildOrder(AbstractUser $user, array $intent): Order
    {
        
        $order = new Order();
        $order->setMerchant($user);
        $order->setIntentId($intent['id']);
        $order->setAmount($intent['amount']);
        $order->setCurrency($intent['currency']);
        $order->setStatus($intent['status']);

        return $order;
    }

    private function addItems(Order $order, array $items, Professional $merchant): void
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

    public function orderPay(Order $order)
    {
        $order->setStatus('paid');
        $order->setPaidAt(new \DateTimeImmutable());



        $this->persist($order);

        $this->em->flush();
    }

    public function validatePayment(Payment $payment)
    {
        $isTransfered = true;
        foreach($payment->getOrders() as $order) {
            if($order->getStatus() !== 'Paid') {
                $isTransfered = false;
            }
        }

        if($isTransfered) {
            $payment->setStatus('Treated');
            $this->em->persist($payment);

            $this->em->flush();
        }
    }

    public function persist(object $order)
    {
        $this->em->persist($order);
    }

    public function updateStatusByIntentId(string $intentId, string $status): void
    {
        $this->orderRepository->updateStatusByIntentId($intentId, $status);
    }

    public function confirmByIntentId(string $intentId): void
    {
        $this->orderRepository->confirmByIntentId($intentId);
    }

    public function getOrderByIntentId(string $intentId)
    {
        return $this->orderRepository->getByIntent($intentId);
    }
}