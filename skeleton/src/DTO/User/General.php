<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User\PostalAdress\Adress;

class General
{
    #[Assert\NotBlank()]
    public ?string $gender  = 'm';
    
    #[Assert\NotBlank()]
    public ?string $firstname  = '';

    #[Assert\NotBlank()]
    public ?string $lastname = '';

    #[Assert\NotBlank()]
    public ?string $username = '';

    public ?Adress $adress = null;

    public function __construct()
    {
        $this->adress = new Adress();
    }
}