<?php

namespace App\Form\Utils\Sql;

use App\Entity\Utils\condition;
use App\Entity\Utils\SqlGenerator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Service\EntityBuilder\EntityMetaDatas;

class ConditionType extends AbstractType
{
    public function __construct(private EntityMetaDatas $metadatas) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('alias', null, [
                'attr' => ['readonly' => true]
            ])
        ;

        $entityReminder = $this->metadatas;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options, $entityReminder) {
            $condition = $event->getData();
            $form = $event->getForm();

            $entityclassName = $condition?->getExtractor()?->getEntityclassName();

            $choices = [$entityclassName => $entityclassName];

            $form->add('alias', ChoiceType::class, [
                'choices' => $options['alias_choices'],
            ]);

            $form->add('field', ChoiceType::class, [
                'choices' => $entityReminder->buildDefaults($options['class_name']),
            ]);

            $form->add('operator', ChoiceType::class, [
                'choices' => condition::OPERATORS
            ])
            ->add('value')
        ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => condition::class,
            'attr' => ['class' => 'input-group justify-content-center'],
            'alias_choices' => [],
            'label' => false,
            'class_name' => ''
        ]);
    }
}
