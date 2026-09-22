<?php

namespace App\Repository;

use App\Entity\User\AbstractUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use App\Entity\User as userContainer;

/**
 * @extends ServiceEntityRepository<AbstractUser>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractUser::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof AbstractUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findAllByType(string $order = 'ASC'): array
    {
        return $this->getEntityManager()
        ->createQuery('
            SELECT u,
                CASE
                    WHEN u INSTANCE OF ' . userContainer\Client::class . ' THEN 1
                    WHEN u INSTANCE OF ' . userContainer\Professional::class . ' THEN 2
                    ELSE 3
                END AS HIDDEN sort_order
            FROM ' . AbstractUser::class . ' u
            ORDER BY sort_order ' . $order . '
        ')
        ->getResult();
    }

    public function getTopDepartmentForClients(): ?array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select(
                'SUBSTRING(a.zipCode, 1, 2) AS codeDepartement',
                'COUNT(a.id) AS total',
                'MIN(a.id) AS firstAdressId'
            )
            ->from(userContainer\Client::class, 'c')
            ->join('c.shippingAdresses', 'a')
            ->groupBy('codeDepartement')
            ->orderBy('total', 'DESC')
            ->addOrderBy('firstAdressId', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function getClientPercentageByDepartment(): array
    {
        $rows = $this->getTopDepartmentForClients();

        $counts = array_column($rows, 'total', 'codeDepartement');

        return $this->withPercentages($counts);
    }

    public function getProPercentageByDepartment(): array
    {
        $rows = $this->getTopDepartmentForProfessionals();

        $counts = array_column($rows, 'total', 'codeDepartement');

        return $this->withPercentages($counts);
    }

    private function withPercentages(array $counts): array
    {
        $total = array_sum($counts);
        $result = [];

        foreach ($counts as $code => $count) {
            $result[] = [
                'code' => $code,
                'total' => (int) $count,
                'percentage' => $total > 0 ? round($count / $total * 100, 2) : 0.0,
            ];
        }

        return $result;
    }

    public function getTopDepartmentForProfessionals(): ?array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('SUBSTRING(a.zipCode, 1, 2) AS codeDepartement', 'COUNT(a.id) AS total')
            ->from(userContainer\Professional::class, 'p')
            ->join('p.adress', 'a')
            ->groupBy('codeDepartement')
            ->getQuery()
            ->getResult();
    }

    public function getStats(): array
    {
        $rows = $this->createQueryBuilder('u')
        ->select('u.roles')
        ->getQuery()
        ->getSingleColumnResult();

        
        $byRole = [];
        foreach ($rows as $rolesJson) {
            $roles = array_filter(
                json_decode($rolesJson),
                fn($r) => $r !== 'ROLE_USER'
            );
            $role = !empty($roles) ? array_values($roles)[0] : 'ROLE_USER';
            $byRole[$role] = ($byRole[$role] ?? 0) + 1;
        }

        arsort($byRole);

        return [
            'total'   => array_sum($byRole),
            'by_role' => $byRole,
        ];
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
