<?php

namespace App\Form\Configuration;

use App\Entity\Configuration\AbstractText;
use App\Entity\Configuration\Homepage\Block;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Service\Configuration\TextConverterService;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\Configuration\MainTitle;
use App\Entity\Configuration\SubTitle;
use App\Entity\Configuration\NormalTitle;
use App\Entity\Configuration\LittleTitle;
use App\Entity\Configuration\Paragraph;
use App\Entity\Configuration\Link;

class AbstractTextType extends AbstractType
{
    public function __construct(
        private TextConverterService $converter
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'row_attr' => [
                    'class' => 'col-lg-4'
                ],
                'mapped'  => false,   // géré par le TextConverterService
                'choices' => [
                    'Main title'    => MainTitle::class,
                    'Subtitle'      => SubTitle::class,
                    'Normal title'  => NormalTitle::class,
                    'Little title'  => LittleTitle::class,
                    'Paragraph'     => Paragraph::class,
                    'Link'          => Link::class,
                ],
            ])
            ->add('color', ColorType::class, [
                'row_attr' => [
                    'class' => 'col-lg-4'
                ]
            ])
            ->add('align', ChoiceType::class, [
                'choices' => [
                    'Start' => 'start',
                    'Center' => 'center',
                    'End' => 'end',
                ],
                'row_attr' => [
                    'class' => 'col-lg-4'
                ]
            ])
            ->add('content', TextareaType::class, [
                'row_attr' => [
                    'class' => 'col-12'
                ]
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();

            // Ne traite que si le formulaire est valide
            if (!$form->isValid()) {
                return;
            }

            $source      = $form->getData();
            $targetClass = $form->get('type')->getData();

            if ($source === null || $targetClass === null) {
                return;
            }

            // Conversion uniquement si le type a changé
            if (!($source instanceof $targetClass)) {
                $this->converter->convertTo($source, $targetClass);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();    // données brutes du formulaire
            $form = $event->getForm();

            if (empty($data['type'])) {
                return;
            }

            $targetClass = $data['type'];
            $current     = $form->getData(); // entité actuelle (peut être null si nouvel élément)
            dump($targetClass);
            // Nouvel élément : on instancie directement la bonne classe
            if ($current === null) {
                $form->setData(new $targetClass());
                return;
            }

            if($targetClass === Link::class) {
                $form->add('target');
            } else {
                $form->remove('target');
            }

            // Élément existant dont le type a changé : on convertit
            if (!($current instanceof $targetClass)) {
                $new = $this->converter->convertTo($current, $targetClass);
                $form->setData($new);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AbstractText::class,
            'label' => false,
            'attr' => [
                'class' => 'row'
            ]
        ]);
    }
}
