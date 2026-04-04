<?php

namespace App\Form\Admin\User;

use App\Entity\User\AbstractUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use App\Entity\User\Professional;

class RolesManagerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'choices' => AbstractUser::getNamedRoles(),
                'label_attr' => ['class' => 'mb-2'],
                'choice_filter' => ChoiceList::filter(
                    // pass the type as first argument
                    $this,
                    function (string $role) use ($builder): bool {
                        $user = $builder->getData();
                        $isProfessional = $user instanceof Professional;

                        if ($role === 'ROLE_ADMIN' || $role === 'ROLE_USER') {
                            return true; // disponible pour tous
                        }

                        if ($role === 'ROLE_SELLER') {
                            return $isProfessional; // uniquement pour Professional
                        }

                        return false;
                    },
                    // pass the option that makes the filter "vary" to compute a unique hash
                    $builder->getData()
                ),
                'choice_attr' => function ($role): array {
                    if($role == "ROLE_USER") {
                        return ['disabled' => 'disabled', 'checked' => 'checked', 'class' => 'mb-2'];
                    }

                    return ['class' => 'mb-2'];
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AbstractUser::class,
        ]);
    }
}
