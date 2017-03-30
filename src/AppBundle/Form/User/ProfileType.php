<?php

namespace AppBundle\Form\User;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profile', ChoiceType::class, [
                'label' => 'user.profile',
                'placeholder' => 'select.placeholder',
                'choices' => [
                    'user.association' => 'association',
                    'user.private' => 'private'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => User::class,
                'validation_groups' => ['Profile']
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_profile_type';
    }
}
