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
                'choices' => $options['sources'],
                "data" => $options['selected_source']
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

            $config = [
                'choices' => $options['sources']
            ];

            if($options['selected_source']) {
                $config['data'] = $options['selected_source'];
            }

            $form->add('source', ChoiceType::class, $config);

            $properties = [];
            $propChoices = [];
            if($selection) {
                foreach($selection->getProperty() as $property) {
                    $properties[$property] = $property;
                }

                $propChoices = $this->metaDatas->buildDefaults(SqlGenerator::getClassNamespace($selection->getSource()));
            }

            $form->add('property', ChoiceType::class, [
                'choices' => $propChoices,
                'multiple' => true,
                'label' => 'Options',
                'data' => $properties,
                'expanded' => true
            ]);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData(); // tableau brut, ex: ['type' => ..., 'source' => 'App\Entity\X', 'property' => [...]]
            $form = $event->getForm();
 
            $submittedSource = $data['source'] ?? null;

            // Revalidation whitelist : on ne fait JAMAIS confiance à la
            // valeur soumise pour résoudre les métadonnées d'une entité.
            $fieldsOptions = array_key_exists($submittedSource, SqlGenerator::CLASSELIST)
                ? $this->metaDatas->buildDefaults(SqlGenerator::CLASSELIST[$submittedSource])
                : [];
 
            $form->add('property', ChoiceType::class, [
                'choices'  => $fieldsOptions,
                'multiple' => true,
                'label'    => 'Options',
                'expanded' => true,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => selector::class,
            'label' => 'Shown informations',
            'fields_options' => $this->metaDatas->buildDefaults(current(SqlGenerator::CLASSELIST)),
            'sources' => [],
            'selected_source' => null
        ]);
    }
}
