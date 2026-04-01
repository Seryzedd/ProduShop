<?php

namespace App\Form\Translations\Product;

use App\Entity\Product\Product;
use App\Entity\Translations\ShelfTranslation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Service\Translation\TranslationFileReader;

class ShelfTranslationType extends AbstractType
{
    public function __construct(private TranslationFileReader $TranslationFileReader) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => $this->TranslationFileReader->getLanguagesByFiles(),
                'row_attr' => ['class' => 'col-md-6']
            ])
            ->add('name', null, [
                'row_attr' => ['class' => 'col-md-6']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShelfTranslation::class,
            'label' => false,
            'attr' => ['class' => 'row']
        ]);
    }
}
