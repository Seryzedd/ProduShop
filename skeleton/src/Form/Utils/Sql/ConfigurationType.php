<?php

namespace App\Form\Utils\Sql;

use App\Entity\User\AbstractUser;
use App\Entity\Utils\SqlGenerator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\Utils\Sql\ConditionType;
use App\Form\Utils\Sql\SelectorType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'row_attr' => ['class' => 'col-6']
            ])
            ->add('description', TextareaType::class, [
                'row_attr' => ['class' => 'col-6'],
                'required' => false
            ])
            ->add('entityclassName', ChoiceType::class, [
                'choices' => SqlGenerator::getEntityclassNames(),
                'row_attr' => ['class' => 'col-6'],
                'label' => 'From',
                'label_attr' => ['class' => '']
            ])
            ->add('selector', CollectionType::class, [
                'entry_type' => SelectorType::class,
                'row_attr' => ['class' => 'col-6'],
                'label' => 'Select',
                'label_attr' => ['class' => ''],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $config = $event->getData();
            $form = $event->getForm();

            $entityclassName = $config->getEntityclassName();

            
            $form->add('conditions', CollectionType::class, [
                'entry_options' => [
                    'alias_choices' => [
                        $entityclassName => $entityclassName
                    ],
                    'class_name' => $config->getClassNamespace($entityclassName)
                ],
                'entry_type' => ConditionType::class,
                'label' => 'Conditions',
                'label_attr' => ['class' => ''],
                'row_attr' => ['class' => 'col-12'],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SqlGenerator::class,
            'attr' => ['class' => 'row']
        ]);
    }
}
