<?php

namespace App\Security;

use App\Entity\User\AbstractUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Symfony appelle cette méthode pour recharger l'utilisateur depuis la session
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AbstractUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Recharger l'utilisateur depuis la base de données
        $reloadedUser = $this->entityManager->getRepository(AbstractUser::class)->find($user->getId());

        if (!$reloadedUser) {
            throw new UserNotFoundException(sprintf('User with ID "%s" could not be reloaded.', $user->getId()));
        }

        return $reloadedUser;
    }

    /**
     * Indique à Symfony quels types d'utilisateurs ce provider supporte
     */
    public function supportsClass(string $class): bool
    {
        return AbstractUser::class === $class || is_subclass_of($class, AbstractUser::class);
    }

    /**
     * Charge l'utilisateur par son identifiant (email dans votre cas)
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->entityManager->getRepository(AbstractUser::class)->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    /**
     * Permet de mettre à jour le hash du mot de passe si nécessaire
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof AbstractUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}