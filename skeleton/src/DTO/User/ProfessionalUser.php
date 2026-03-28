<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User\PostalAdress\Adress;
use App\Validator\Siret;
use App\Entity\Picture;

class ProfessionalUser
{
    #[Assert\NotBlank()]
    public string $name  = '';

    #[Assert\NotBlank()]
    #[Assert\Length(min: 14, max: 14)]
    #[Siret]
    public string $siret = '';

    public ?Adress $adress = null;

    public Picture $logo;

    public function __construct()
    {
        $this->adress = new Adress();
        $this->logo = new Picture();
    }
}