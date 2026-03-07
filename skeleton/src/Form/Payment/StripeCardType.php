<?php

namespace App\Form\Payment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;

class StripeCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = $this->buildChoices($options['payment_methods']);

        $builder
            ->add('paymentMethodId', ChoiceType::class, [
                'label'       => false,
                // "new" est toujours présent, qu'il y ait des cartes ou non
                'choices'     => $choices,
                'expanded'    => true,
                'multiple'    => false,
                // Pré-sélectionne la première carte enregistrée, sinon "new"
                'data'        => array_key_first(array_flip($choices)),
                'constraints' => [new NotBlank()],
            ])
            // Champ caché alimenté par Stripe.js uniquement quand "new" est sélectionné
            ->add('newPaymentMethodId', HiddenType::class, [
                'attr'     => ['id' => 'new-payment-method-id'],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'payment',
            'payment_methods' => [],
        ]);

        $resolver->setAllowedTypes('payment_methods', 'array');
    }

    /**
     * Construit les choices depuis les méthodes Stripe + toujours l'option "new".
     * "new" est en dernier si des cartes existent, en premier sinon.
     */
    private function buildChoices(array $paymentMethods): array
    {
        $choices = [];

        foreach ($paymentMethods as $method) {
            $card  = $method['card'];
            $label = sprintf(
                '%s •••• %s  (exp. %02d/%d)',
                ucfirst($card['brand']),
                $card['last4'],
                $card['exp_month'],
                $card['exp_year']
            );
            $choices[$label] = $method['id'];
        }

        $choices['New payment method'] = 'new';

        return $choices;
    }
}
