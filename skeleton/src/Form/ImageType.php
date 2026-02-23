<?php

namespace App\Form;

use App\Entity\Picture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Service\ImgTransformerService;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageType extends AbstractType
{
    public function __construct(private ImgTransformerService $imgTransformerService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Image file',
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'd-none'
                ],
                'label_attr' => [
                    'class' => 'image-form'
                ],
                'constraints' => [
                    new File(
                        mimeTypes: ['image/*'],
                        mimeTypesMessage: 'Please upload a valid image file.'
                    ),
                ],
                'mapped' => false,
                'required' => false,
            ])
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $picture = $event->getData();

                $form->add('file', FileType::class, [
                    'label' => $picture ? $picture->getName() : 'New image',
                    'label_attr' => [
                        'data-current-image' => $picture ? $picture->getSrc() : '',
                    ],
                    'attr' => [
                        'accept' => 'image/*',
                        'class' => 'd-none'
                    ],
                    'row_attr' => [
                        'class' => 'mb-0',
                    ],
                    'constraints' => [
                        new File(
                            mimeTypes: ['image/*'],
                            mimeTypesMessage: 'Please upload a valid image file.'
                        ),
                    ],
                    'mapped' => false,
                    'required' => false,
                ]);
            }
        );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $picture = $form->getData();

                $file = $form->get('file')->getData();
                
                if (!$file) {
                    return;
                }
                
                if ($picture === null) {
                    $picture = new Picture(
                        $file->getClientOriginalName(),
                        $this->imgTransformerService->fileToBase64($file->getPathname()),
                        $file->guessExtension()
                    );
                } else {
                    $picture->setName($file->getClientOriginalName());
                    $picture->setSrc($this->imgTransformerService->fileToBase64($file->getPathname()));
                    $picture->setExtension($file->guessExtension());
                }

                $event->setData($picture);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
        ]);
    }
}
