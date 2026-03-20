<?php

namespace App\Repository\User;

use App\Entity\User\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User\Professional;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByMerchantWithItems(Professional $merchant): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.merchant = :merchant')
            ->setParameter('merchant', $merchant)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function updateStatusByIntentId(string $intentId, string $status): void
    {
        $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', ':status')
            ->where('o.intentId = :intentId')
            ->setParameter('status', $status)
            ->setParameter('intentId', $intentId)
            ->getQuery()
            ->execute();
    }

    public function getByIntent(string $intentId)
    {
        return $this->findOneBy(['intentId' => $intentId]);
    }
 
    /**
     * Met à jour le statut à 'paid' et renseigne paidAt en une seule requête.
     */
    public function confirmByIntentId(string $intentId): void
    {
        $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', ':status')
            ->set('o.paidAt', ':paidAt')
            ->where('o.intentId = :intentId')
            ->setParameter('status', 'paid')
            ->setParameter('paidAt', new \DateTimeImmutable())
            ->setParameter('intentId', $intentId)
            ->getQuery()
            ->execute();
    }
}
