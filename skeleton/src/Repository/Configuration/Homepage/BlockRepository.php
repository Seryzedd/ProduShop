<?php

namespace App\Repository\Configuration\Homepage;

use App\Entity\Configuration\Homepage\Block;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Block>
 */
class BlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Block::class);
    }

    /**
     * @return Block[] Returns an array of Block objects
     */
    public function findByOrdered(array $filters = [], string $order = 'ASC'): array
    {
        $query = $query = $this->getQuery($filters);

        return $query
            ->orderBy('b.position', $order)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Block[] Returns an array of Block objects
     */
    public function findOneByOrdered(array $filters = [], string $order = 'ASC'): array
    {
        $query = $this->getQuery($filters);

        return $query
            ->orderBy('b.position', $order)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function getQuery(array $params)
    {
        $query = $this->createQueryBuilder('b');

        foreach ($params as $key => $value) {
            $query->andWhere('b.' . $key . ' = '. $value)
            ;
        }

        return $query;
    }

    //    public function findOneBySomeField($value): ?Block
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
