<?php

namespace App\Form\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Flow\FormFlowCursor;
use Symfony\Component\Form\Flow\Type\FinishFlowType;
use Symfony\Component\Form\Flow\Type\ResetFlowType;
use Symfony\Component\Form\Flow\Type\NextFlowType;
use Symfony\Component\Form\Flow\Type\PreviousFlowType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NavigatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reset', ResetFlowType::class, [
                'attr' => ['class' => 'reset btn-light']
            ])
            ->add('previous', PreviousFlowType::class, [
                'label' => 'Previous',
                'attr' => ['class' => 'prev btn-outline-dark'],
                'include_if' => fn (FormFlowCursor $cursor) => !$cursor->isFirstStep(),
            ])
            ->add('next', NextFlowType::class, [
                'label' => 'Next',
                'attr' => ['class' => 'next btn-outline-dark'],
                'include_if' => fn (FormFlowCursor $cursor) => !$cursor->isLastStep(),
            ])
            ->add('finish', FinishFlowType::class, [
                'label' => 'Finish',
                'attr' => ['class' => 'validate-btn btn-primary'],
                'include_if' => fn (FormFlowCursor $cursor) => $cursor->isLastStep(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'mapped' => false,
        ]);
    }
}