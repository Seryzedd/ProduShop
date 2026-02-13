<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Validator\PasswordValidator;

final class PasswordValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Password) {
            throw new UnexpectedTypeException($constraint, StrongPassword::class);
        }

        // Valeur null ou vide autorisée (utilisez NotBlank si nécessaire)
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $violations = [];

        // Vérifier la longueur minimale
        if (mb_strlen($value) < $constraint->minLength) {
            $this->context->buildViolation($constraint->tooShortMessage)
                ->setParameter('{{ limit }}', $constraint->minLength)
                ->addViolation();
            $violations[] = 'length';
        }

        // Vérifier la présence d'une majuscule
        if (!preg_match('/[A-Z]/', $value)) {
            $this->context->buildViolation($constraint->missingUppercaseMessage)
                ->addViolation();
            $violations[] = 'uppercase';
        }

        // Vérifier la présence d'une minuscule
        if (!preg_match('/[a-z]/', $value)) {
            $this->context->buildViolation($constraint->missingLowercaseMessage)
                ->addViolation();
            $violations[] = 'lowercase';
        }

        // Vérifier la présence d'un caractère spécial
        $specialCharsPattern = '/[' . preg_quote($constraint->specialChars, '/') . ']/';
        if (!preg_match($specialCharsPattern, $value)) {
            $this->context->buildViolation($constraint->missingSpecialCharMessage)
                ->addViolation();
            $violations[] = 'special';
        }
    }
}
