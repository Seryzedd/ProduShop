<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\PasswordValidator;

class PasswordStep
{
    
    #[PasswordValidator(
        minLength: 10,
        specialChars: '!;?,:%*$@&éàç'
    )]
    public string $password  = '';
}