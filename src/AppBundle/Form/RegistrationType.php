<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RegistrationType extends EditType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'user.email'
            ])
            ->add('captcha', CaptchaType::class, [
                'label' => 'captcha'
            ]);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => User::class,
                'validation_groups' => ['Default', 'Registration']
            ]
        );
    }
}
