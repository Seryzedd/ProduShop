<?php

namespace App\Form\Utils\Sql;

use App\Entity\Utils\selector;
use App\Entity\Utils\SqlGenerator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Service\EntityBuilder\EntityMetaDatas;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SelectorType extends AbstractType
{
    public function __construct(private EntityMetaDatas $metaDatas) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => selector::TYPES
            ])
            ->add('source', ChoiceType::class, [
                'choices' => $options['sources']
            ])
            ->add('property', ChoiceType::class, [
                'choices' => $options['fields_options'],
                'multiple' => true,
                'label' => 'Options',
                'expanded' => true
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $selection = $event->getData();
            $form = $event->getForm();

            $form->add('source', ChoiceType::class, [
                'choices' => $options['sources'],
            ]);

            
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => selector::class,
            'label' => 'Shown informations',
            'fields_options' => $this->metaDatas->buildDefaults(current(SqlGenerator::CLASSELIST)),
            'sources' => []
        ]);
    }
}
