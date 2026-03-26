<?php

namespace App\Form\Configuration;

use App\Entity\Configuration\Homepage\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\ImageType;
use App\Form\Configuration\AbstractTextType;

class BlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('backgroundColor', ColorType::class, [])
            ->add('type',ChoiceType::class, [
                'choices' => Block::TYPE,
                'multiple' => false
            ])
            ->add('active')
            ->add('backgroundImage', ImageType::class, [
                'label' => false,
            ])
            ->add('htmlElement', CollectionType::class, [
                'entry_type' => AbstractTextType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
        ]);
    }
}
