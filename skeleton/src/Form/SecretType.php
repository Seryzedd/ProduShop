<?php

namespace App\Form;

use App\Form\DataTransformer\MaskedSecretTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class SecretType extends AbstractType
{
    private const MASK_CHAR   = '*';
    private const MASK_LENGTH = 24;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $submitted = trim((string) $event->getData());

            if ($submitted === '' || str_contains($submitted, self::MASK_CHAR)) {
                $event->setData($event->getForm()->getData());
            }
        });
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

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // La vraie valeur n'est jamais envoyée dans le HTML
        $view->vars['value'] = $this->mask(
            $form->getData(),
            $options['prefix_length'],
            $options['suffix_length']
        );
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'secret';
    }

    private function mask(mixed $value, int $prefixLength, int $suffixLength): string
    {
        if (!is_string($value) || $value === '') {
            return '';
        }

        if (mb_strlen($value) <= $prefixLength + $suffixLength) {
            return str_repeat(self::MASK_CHAR, self::MASK_LENGTH);
        }

        return mb_substr($value, 0, $prefixLength)
            . str_repeat(self::MASK_CHAR, self::MASK_LENGTH)
            . mb_substr($value, -$suffixLength);
    }
}
