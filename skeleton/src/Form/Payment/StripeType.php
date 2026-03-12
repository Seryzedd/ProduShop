<?php

namespace App\Form\Payment;

use App\Entity\Payment\Stripe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PercentType;

class StripeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('authenticationKey')
            ->add('feesAmount', PercentType::class, [
                'scale' => 2,
                'type' => 'integer',
                'html5' => true
            ])
            ->add('publicKey')
            ->add('secretKey')
            ->add('active')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stripe::class,
        ]);
    }
}
