<?php

namespace App\DTO\User;

use App\DTO\User\CustomerSign;
use App\DTO\User\ProfessionalUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\DTO\User\PasswordStep;
use App\DTO\User\General;
use App\Entity\User;

class UserSignUp
{
    #[Valid(groups: ['basics'])]
    public CustomerSign $basics;

    #[Valid(groups: ['general'])]
    public General $general;

    #[Valid(groups: ['professional'])]
    public ProfessionalUser $professional;

    #[Valid(groups: ['adress'])]
    public User\PostalAdress\Adress $adress;

    #[Valid(groups: ['security'])]
    public PasswordStep $security;

    public string $currentStep = 'basics';

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->basics = new CustomerSign();
        $this->professional = new ProfessionalUser();
        $this->adress = new User\PostalAdress\Adress();
        $this->passwordStep = new PasswordStep();
        $this->hasher = $hasher;
    }

    public function getUser(): User\AbstractUser
    {
        if(!$this->basics->professional) {
            $user = new User\Client();
            $user->setGender($this->general->gender);
            $user->setFirstname($this->general->firstname);
            $user->setLastname($this->general->lastname);
            $user->addShippingAdress($this->adress);
        } else {
            $user = new User\Professional();

            $user->setLogo($this->professional->logo);
            $user->setSiret($this->professional->siret);
            $user->setCompanyName($this->professional->name);
            $user->setAdress($this->adress);
        }

        $user->setEmail($this->basics->email);
        $user->setPassword($this->hasher->hashPassword($user, $this->security->password));

        return $user;
    }
}