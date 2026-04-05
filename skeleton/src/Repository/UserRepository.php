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
