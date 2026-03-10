<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class FrenchPhoneValidator extends ConstraintValidator
{
    private const FRENCH_PHONE_REGEX = '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.\-]?\d{2}){4}$/';
    
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof FrenchPhone) {
            throw new UnexpectedTypeException($constraint, FrenchPhone::class);
        }

        if (null === $value || '' === $value) {
            return; // Géré par NotBlank si nécessaire
        }

        if (!preg_match(self::FRENCH_PHONE_REGEX, (string) $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
