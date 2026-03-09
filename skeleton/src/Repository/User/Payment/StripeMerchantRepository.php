<?php

namespace App\Repository\User\Payment;

use App\Entity\User\Payment\StripeMerchant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User\Professional;

/**
 * @extends ServiceEntityRepository<StripeMerchant>
 */
class StripeMerchantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StripeMerchant::class);
    }

    public function findByProfessional(Professional $professional): ?StripeMerchant
    {
        return $this->findOneBy(['user' => $professional]);
    }

    //    /**
    //     * @return StripeMerchant[] Returns an array of StripeMerchant objects
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

    //    public function findOneBySomeField($value): ?StripeMerchant
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
