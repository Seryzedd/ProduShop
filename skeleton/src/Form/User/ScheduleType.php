<?php

namespace App\Form\User;

use App\Entity\User\OpeningSchedule;
use App\Entity\User\Professional;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\User\DayType;
use Symfony\Component\Validator\Constraints\Valid;

class ScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scheduleDays', CollectionType::class, [
                'entry_type' => DayType::class,
                'label' => false,
                'by_reference' => false,
                'constraints' => [new Valid()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OpeningSchedule::class,
            'label' => false
        ]);
    }
}
