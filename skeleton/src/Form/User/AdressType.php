<?php

namespace App\Form\User;

use App\Entity\User\Client;
use App\Entity\User\PostalAdress\Adress;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'empty_data' => ''
            ])
            ->add('zipCode', TextType::class, [
                'row_attr' => ['class' => ''],
                'empty_data' => ''
            ])
            ->add('country', TextType::class, [])
            ->add('complement', TextType::class, [
                'required' => false,
                'empty_data' => ''
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adress::class,
        ]);
    }
}
