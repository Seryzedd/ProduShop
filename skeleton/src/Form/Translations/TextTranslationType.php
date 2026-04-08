<?php

namespace App\Form\Translations;

use App\Entity\Translations\TextTranslation;
use App\Entity\Configuration\Homepage\Block;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Service\Translation\TranslationFileReader;

class TextTranslationType extends AbstractType
{
    public function __construct(private TranslationFileReader $TranslationFileReader) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => $this->TranslationFileReader->getLanguagesByFiles(),
                'row_attr' => ['class' => 'col-md-2', 'rows' => 6]
            ])
            ->add('content', TextareaType::class, [
                'row_attr' => ['class' => 'col-md-10']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TextTranslation::class,
            'attr' => ['class' => 'row align-items-center'],
            'label' => false
        ]);
    }
}
