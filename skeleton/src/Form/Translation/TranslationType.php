<?php

namespace App\Form\Translation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\DTO\Translations\TranslationDTO;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('translationKey', TextType::class, [
                'row_attr' => ['class' => 'col-lg-6']
            ])
            ->add('translationValue', TextType::class, [
                'row_attr' => ['class' => 'col-lg-6']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => TranslationDTO::class,
            'attr' => ['class' => 'row'],
            'label' => false
        ]);
    }
}
