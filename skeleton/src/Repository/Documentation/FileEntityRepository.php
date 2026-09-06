<?php

namespace App\Repository\Documentation;

use App\Entity\Documentation\FileEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileEntity>
 */
class FileEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileEntity::class);
    }

    public function findAllByTypes(): array
    {

        $response = [];
        
        foreach($this->findAllByDate() as $file) {
            $response[$file->getType()][$file->getcreatedAt()->format('Y')][$file->getcreatedAt()->format('F')][] = $file;
        }

        return $response;
    }

    public function findAllLatest(): array
    {
        $subQuery = $this->createQueryBuilder('f2')
            ->select('MAX(f2.createdAt)')
            ->where('f2.type = f.type')
            ->getDQL();
 
        return $this->createQueryBuilder('f', 'f.type')
            ->where("f.createdAt = ({$subQuery})")
            ->orderBy('f.type', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function findAllByDate(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.type', 'ASC')
            ->addOrderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return FileEntity[] Returns an array of FileEntity objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FileEntity
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
