<?php

namespace App\Form\Helper;

use App\Entity\Helper\Documentation;
use App\Entity\Helper\Step;
use App\Entity\Picture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\ImageType;

class StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', null, [
                'row_attr' => ['class' => 'col-12'],
                'attr' => ['class' => 'input-position d-none'],
                'label' => false,
                'data' => 0,
            ])
            ->add('title', null, [
                'row_attr' => ['class' => 'col-12']
            ])
            ->add('text', null, [
                'row_attr' => ['class' => 'col-6'],
                'attr' => ['rows' => 6]
            ])
            ->add('image', ImageType::class, [
                'label' => false,
                'row_attr' => ['class' => 'col-6 text-center']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Step::class,
            'attr' => ['class' => 'row align-items-center'],
            'label' => false
        ]);
    }
}
