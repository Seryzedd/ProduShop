<?php

namespace App\Form\Step;

use App\Form\Step\CustomerType;
use App\Form\Step\NavigatorType;
use App\Form\Step\GeneralType;
use App\Form\Step\PasswordStepType;
use App\Form\Step\ProfessionalType;
use App\DTO\User\UserSignUp;
use Symfony\Component\Form\Flow\AbstractFlowType;
use Symfony\Component\Form\Flow\FormFlowBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\User\AdressType;

class RegistrationStepType extends AbstractFlowType
{
    public function buildFormFlow(FormFlowBuilderInterface $builder, array $options): void
    {
        // Étape 1 : Email + Custom/pro choice
        $builder->addStep('basics', CustomerType::class);

        // Étape 2 : General details for customer user
        // skipped if user is pro
        $builder->addStep('general', GeneralType::class, skip: fn (UserSignUp $data) => $data->basics->professional == true);

        // Étape 3 : Professional informations
        // skipped if user is customer
        $builder->addStep('professional', ProfessionalType::class, skip: fn (UserSignUp $data) => $data->basics->professional == false);

        // Etape 4 : user's Postal adress
        $builder->addStep('adress', AdressType::class);

        // Étape 5 : user's password
        $builder->addStep('security', PasswordStepType::class);

        // Ajout du navigateur
        $builder->add('navigator', NavigatorType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSignUp::class,
            'step_property_path' => 'currentStep'
        ]);
    }
}