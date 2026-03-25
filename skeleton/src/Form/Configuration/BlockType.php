<?php

namespace App\Form\Configuration;

use App\Entity\Configuration\Homepage\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\ImageType;

class BlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text')
            ->add('textColor', ColorType::class, [])
            ->add('backgroundColor', ColorType::class, [])
            ->add('type',ChoiceType::class, [
                'choices' => Block::TYPE,
                'multiple' => false
            ])
            ->add('active')
            ->add('backgroundImage', ImageType::class, [
                'label' => false,
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
