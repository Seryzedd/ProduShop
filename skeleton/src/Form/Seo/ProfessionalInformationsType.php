<?php

namespace App\Form\Seo;

use App\Entity\User\Professional;
use App\Entity\User\Seo\SeoProfessionalInformations;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfessionalInformationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('meta', null, [
                'attr' => [
                    'rows' => 6,
                    'class' => 'text-preview'
                ]
            ])
            ->add('title', null, [
                'label' => 'webpage title'
            ])
            ->add('logoTag', null, [
                'help' => 'Text appear on your professional\'s logo'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeoProfessionalInformations::class,
        ]);
    }
}
