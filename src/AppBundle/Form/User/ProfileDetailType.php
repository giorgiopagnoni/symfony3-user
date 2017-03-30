<?php

namespace AppBundle\Form\User;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('profile', HiddenType::class, [
            'required' => true
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            /** @var User $user */
            $user = $event->getData();
            $profile = $user->getProfile();

            if ($profile == 'private') {
                $form->add('fullname', TextType::class);
            } else if ($profile == 'association') {
                $form->add('association', TextType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => function (FormInterface $form) {
                $profile = $form->get('profile')->getData();

                if ($profile == 'private') {
                    return ['Private'];
                }
                return ['Association'];
            },
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_profile_detail_type';
    }
}
