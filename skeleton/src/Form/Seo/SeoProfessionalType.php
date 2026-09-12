<?php

namespace App\Form\Seo;

use App\Entity\Picture;
use App\Entity\User\OpeningSchedule;
use App\Entity\User\Payment\StripeMerchant;
use App\Entity\User\PostalAdress\Adress;
use App\Entity\User\Professional;
use App\Entity\User\Seo\SeoProfessionalInformations;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Seo\ProfessionalInformationsType;
use App\Form\Seo\SeoKeywordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SeoProfessionalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seoInformations', ProfessionalInformationsType::class, [])
            ->add('seoKeywords', CollectionType::class,
                [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_type' => SeoKeywordType::class,
                    'label' => 'keywords'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Professional::class,
        ]);
    }
}
