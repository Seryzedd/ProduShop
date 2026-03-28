<?php

namespace App\Form\Translation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PrototypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('translations', CollectionType::class, [
            'entry_type' => TranslationType::class,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'data' => [], // ← CRUCIAL : aucune donnée réelle, juste le prototype
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
