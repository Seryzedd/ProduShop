<?php

namespace App\Form\Product;

use App\Entity\Picture;
use App\Entity\Product\Product;
use App\Entity\Product\Shelf;
use App\Entity\Product\Slider;
use App\Entity\User\Professional;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\Product\PackageType;

class ProductsStocksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('packages', CollectionType::class, [
                // each entry in the array will be an "email" field
                'entry_type' => PackageType::class,
                'label' => false,
                'by_reference' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'label' => false
        ]);
    }
}
