<?php

namespace App\Repository\Payment;

use App\Entity\Payment\Stripe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stripe>
 */
class StripeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stripe::class);
    }

    public function findStripe(): Stripe
    {
        $stripe = $this->createQueryBuilder('stripe')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if(!$stripe) {
            return new Stripe();
        }

        return $stripe;
    }

    //    /**
    //     * @return Stripe[] Returns an array of Stripe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Stripe
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
