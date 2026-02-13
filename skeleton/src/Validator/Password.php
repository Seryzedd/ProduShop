<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute()]
final class Password extends Constraint
{
    
    public string $message = 'Le mot de passe ne respecte pas les critères de sécurité.';
    public string $tooShortMessage = 'Le mot de passe doit contenir au moins {{ limit }} caractères.';
    public string $missingUppercaseMessage = 'Le mot de passe doit contenir au moins une majuscule.';
    public string $missingLowercaseMessage = 'Le mot de passe doit contenir au moins une minuscule.';
    public string $missingSpecialCharMessage = 'Le mot de passe doit contenir au moins un caractère spécial (!;?,:%*$@&éàç).';
    
    public int $minLength = 10;
    public string $specialChars = '!;?,:%*$@&éàç';

    // You can use #[HasNamedArguments] to make some constraint options required.
    // All configurable options must be passed to the constructor.
    public function __construct(
        public string $mode = 'strict',
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }
}
