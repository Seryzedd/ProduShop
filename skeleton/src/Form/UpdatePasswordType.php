<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use App\Form\ChangePasswordFormType;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Current password',
                'constraints' => [
                    new Assert\NotBlank(message: 'Please enter your current password'),
                    new SecurityAssert\UserPassword(message: 'The typed password is incorrect.'),
                ],
                'help' => 'Enter your current password to confirm the change',
                'attr' => [
                    'autocomplete' => false,
                    'placeholder' => 'myCurrentPassword?$123',
                ],
            ])
            ->add('newPassword', ChangePasswordFormType::class, [
                'mapped' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
