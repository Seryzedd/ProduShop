<?php

// src/Security/UserChecker.php
namespace App\Security;

use App\Entity\User\AbstractUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        
        if (!$user instanceof AbstractUser) {
            return;
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'email_not_verified'
            );
        }
    }

    public function checkPostAuth(UserInterface $user, TokenInterface $token = null): void
    {
        // rien à faire ici
    }
}