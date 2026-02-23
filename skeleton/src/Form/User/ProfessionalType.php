<?php

namespace App\Form\User;

use App\Entity\Picture;
use App\Entity\User\PostalAdress\Adress;
use App\Entity\User\Professional;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\ImageType;

class ProfessionalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('logo', ImageType::class, [])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'example@contact.com'
                ],
                'disabled' => true
            ])
            ->add('siret', TextType::class, [
                'attr' => [
                    'class' => 'siret',
                    'placeholder' => 'xxx xxx xxx xxxxx'
                ],
                'help' => 'Siret of company are numbers only',
                'empty_data' => ''
            ])
            ->add('companyName', TextType::class, [
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'My company name'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Professional::class,
        ]);
    }
}
