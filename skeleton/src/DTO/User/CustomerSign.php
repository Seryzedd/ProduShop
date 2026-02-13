<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class CustomerSign
{
    
    #[Assert\NotBlank()]
    public string $email  = '';

    public ?bool $professional = null;
}