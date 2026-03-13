<?php

namespace App\Form\User;

use App\Entity\User\Schedule\Hours;
use App\Entity\User\Schedule\ScheduleDay;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class HoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('startHour', ChoiceType::class, [
                'row_attr' => ['class' => 'col-md-3'],
                'choices' => $this->getHours()
            ])
            ->add('startMinutes', ChoiceType::class, [
                'row_attr' => ['class' => 'col-md-3'],
                'choices' => $this->getMinutes()
            ])
            ->add('endHour', ChoiceType::class, [
                'row_attr' => ['class' => 'col-md-3'],
                'choices' => $this->getHours()
            ])
            ->add('endMinutes', ChoiceType::class, [
                'row_attr' => ['class' => 'col-md-3'],
                'choices' => $this->getMinutes()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hours::class,
            'label' => false,
            'attr' => ['class' => 'row']
        ]);
    }

    private function getHours(): array
    {
        $choices = [];

        for ($i=0; $i <= 24; $i++) { 
            $choices[$i] = $i;
        }

        return $choices;
    }

    private function getMinutes(): array
    {
        $choices = [];

        for ($i=0; $i <= 60; $i++) { 
            $choices[$i] = $i;
        }

        return $choices;
    }
}
