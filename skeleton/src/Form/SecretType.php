<?php

namespace App\Form;

use App\Form\DataTransformer\MaskedSecretTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SecretType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new MaskedSecretTransformer(
                originalValue: $options['current_value'],
                prefixLength: $options['prefix_length'],
                suffixLength: $options['suffix_length'],
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'current_value'  => null,   // valeur réelle actuelle, injectée depuis l'entité
            'prefix_length'  => 7,      // ex: "sk_live_"
            'suffix_length'  => 4,      // 4 derniers caractères réels
            'required'       => false,
            'attr'           => [
                'autocomplete' => 'off',
                'class' => 'secret-type'
            ],
        ]);

        $resolver->setAllowedTypes('current_value', ['null', 'string']);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'secret';
    }
}
