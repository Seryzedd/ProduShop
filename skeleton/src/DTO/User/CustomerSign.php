<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\FrenchPhone;

class CustomerSign
{
    
    #[Assert\NotBlank()]
    public string $email  = '';

    public ?bool $professional = null;

    #[Assert\NotBlank()]
    #[FrenchPhone]
    public string $phone = '';
}