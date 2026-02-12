<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User\PostalAdress\Adress;
use App\Validator\Siret;

class ProfessionalUser
{
    #[Assert\NotBlank()]
    public string $name  = '';

    #[Assert\NotBlank()]
    #[Assert\Length(min: 14, max: 14)]
    #[Siret]
    public string $siret = '';

    public ?Adress $adress = null;

    public function __construct()
    {
        $this->adress = new Adress();
    }
}