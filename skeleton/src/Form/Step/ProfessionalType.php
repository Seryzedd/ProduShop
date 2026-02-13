<?php

namespace App\Form\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\User\AdressType;
use App\DTO\User\ProfessionalUser;

class ProfessionalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'My company name'
                ]
            ])
            ->add('siret', TextType::class, [
                'attr' => [
                    'class' => 'siret',
                    'placeholder' => 'xxx xxx xxx xxxxx'
                ],
                'help' => 'Siret of company are numbers only',
                'empty_data' => ''
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfessionalUser::class,
            'label' => false
        ]);
    }
}
