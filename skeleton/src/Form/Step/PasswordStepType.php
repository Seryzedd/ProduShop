<?php

namespace App\Form\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\DTO\User\PasswordStep;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class PasswordStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'The password fields must match.',
            'options' => ['attr' => ['class' => 'password-field']],
            'required' => true,
            
            'first_options'  => [
                'label' => 'Password',
                'empty_data' => '',
                ],
            'second_options' => [
                'empty_data' => '',
                'label' => 'Repeat Password'
                ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PasswordStep::class,
            'label' => false
        ]);
    }
}