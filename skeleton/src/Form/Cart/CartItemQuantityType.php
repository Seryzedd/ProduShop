<?php

namespace App\Form\Cart;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\DTO\Cart\CartItem;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class CartItemQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', NumberType::class, [
                'html5' => true,
                'label' => 'x',
                'row_attr' => [
                    'class' => 'input-group mb-0'
                ],
                'attr' => [
                    'min' => 0
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CartItem::class,
            'attr' => [
                'class' => 'mb-0'
            ],
            'label' => false
        ]);
    }
}
