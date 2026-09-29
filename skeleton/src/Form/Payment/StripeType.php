<?php

namespace App\Form\Payment;

use App\Entity\Payment\Stripe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use App\Form\SecretType;

class StripeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Stripe|null $config */
        $config = $options['data'] ?? null;

        $builder
            ->add('webhookSecret', SecretType::class, [
                'current_value' => $config?->getWebhookSecret(),
                'prefix_length' => 6,
            ])
            ->add('feesAmount', PercentType::class, [
                'scale' => 2,
                'type' => 'integer',
                'html5' => true
            ])
            ->add('publicKey')
            ->add('secretKey', SecretType::class, [
                'current_value' => $config?->getSecretKey(),
                'prefix_length' => 8
            ])
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
