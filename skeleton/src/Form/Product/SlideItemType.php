<?php

namespace App\Form\Product;

use App\Entity\Picture;
use App\Entity\Product\SlideItem;
use App\Entity\Product\Slider;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\ImageType;

class SlideItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', ImageType::class, [
                'label' => false,
                'row_attr' => [
                    'class' => 'mb-0',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SlideItem::class,
            'label' => false,
            'row_attr' => [
                'class' => 'mb-0',
            ],
        ]);
    }
}
