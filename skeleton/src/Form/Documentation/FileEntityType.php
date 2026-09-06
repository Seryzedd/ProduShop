<?php

namespace App\Form\Documentation;

use App\Entity\Documentation\FileEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;

class FileEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices'  => FileEntity::FILE_TYPE,
                'label' => false,
                'row_attr' => ['class' => 'mx-2']
            ])
            ->add('file', FileType::class, [
                'mapped' => false,
                'label' => false,
                'row_attr' => ['class' => 'mx-2'],
                'constraints' => [
                    new File(
                        maxSize: '1024k',
                        mimeTypes: [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        mimeTypesMessage: 'Merci de déposer un fichier PDF valide.',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FileEntity::class,
            'attr' => ['class' => 'd-flex justify-content-end mb-2']
        ]);
    }
}
