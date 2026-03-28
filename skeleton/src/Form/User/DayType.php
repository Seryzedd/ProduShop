<?php

namespace App\Form\User;

use App\Entity\User\OpeningSchedule;
use App\Entity\User\Schedule\ScheduleDay;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\User\HoursType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Valid;

class DayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $scheduleDay = $event->getData();
            $form = $event->getForm();

            $dayNumber = $scheduleDay?->getDay() ?? 0;
            $label = ScheduleDay::_DAYS[$dayNumber] ?? 'Jour ' . $dayNumber;

            $form->add('hours', CollectionType::class, [
                'entry_type' => HoursType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label_attr' => ['class' => 'fw-bold text-primary'],
                'by_reference' => false,
                'constraints' => [new Valid()]
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScheduleDay::class,
            'label' => false
        ]);
    }
}
