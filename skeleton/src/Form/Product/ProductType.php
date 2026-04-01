<?php

namespace App\Form\Product;

use App\Entity\Picture;
use App\Entity\Product\Product;
use App\Entity\Product\Slider;
use App\Entity\User\Professional;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\Product\PackageType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use App\Entity\Product\Shelf;
use App\Form\ImageType;
use App\Form\Translations\Product\ProductTranslationType;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductType extends AbstractType
{
    public function __construct(private RequestStack $requestStack) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'fr';

        $builder
            ->add('name', FormTypes\TextType::class, [
                'attr' => ['placeholder' => 'Product name'],
            ])
            ->add('description', FormTypes\TextareaType::class, [
                'attr' => ['rows' => 5],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('slider', EntityType::class, [
                'class' => Slider::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'No slider',
            ])
            ->add('image', ImageType::class, [
                'label' => false,
            ])
            ->add('packages', CollectionType::class, [
                'entry_type' => PackageType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('shelf', EntityType::class, [
                'class' => Shelf::class,
                'choice_label' => function (Shelf $shelf) use ($locale): string {
                    return $shelf->translate($locale) ? $shelf->translate($locale)->getName() : $shelf->getName();
                },
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ProductTranslationType::class,
                'label' => false,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
