<?php

namespace App\Form\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\User\AdressType;
use App\Entity\User\Client;
use App\DTO\User\General;

class GeneralType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => Client::__GENDER,
                'expanded' => true,
                'multiple' => false,
                'data' => 'm',
                'required' => true,
                'empty_data' => ''
            ])
            ->add('username', TextType::class, [])
            ->add('firstname', TextType::class, [])
            ->add('lastname', TextType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => General::class,
            'label' => false
        ]);
    }
}
