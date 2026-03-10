<?php

namespace App\Form\Product;

use App\Entity\Product\Package;
use App\Entity\Product\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'row_attr' => ['class' => 'col-12'],
            ])
            ->add('quantity', FormTypes\NumberType::class, [
                'attr' => ['min' => 0],
                'help' => 'Quantity items included',
                'html5' => true,
                'empty_data' => 0,
                'row_attr' => ['class' => 'col-lg-3'],
            ])
            ->add('stock', FormTypes\NumberType::class, [
                'attr' => ['min' => 0],
                'help' => 'Number of items in stock',
                'html5' => true,
                'empty_data' => 0,
                'row_attr' => ['class' => 'col-lg-3'],
            ])
            ->add('price', FormTypes\MoneyType::class, [
                'currency' => 'EUR',
                'help' => 'Price of this package',
                'attr' => ['min' => 0],
                'empty_data' => 0,
                'html5' => true,
                'row_attr' => ['class' => 'col-lg-3'],
            ])
            ->add('taxe', FormTypes\PercentType::class, [
                'attr' => ['min' => 0],
                'rounding_mode' => 2,
                'attr' => [
                    'step' => 0.5
                ],
                'help' => 'Tax rate',
                'empty_data' => 0,
                'html5' => true,
                'row_attr' => ['class' => 'col-lg-3'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Package::class,
            'attr' => ['class' => 'row align-items-start'],
            'label' => false,
        ]);
    }
}
