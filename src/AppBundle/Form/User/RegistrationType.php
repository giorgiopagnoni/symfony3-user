<?php

namespace AppBundle\Form\User;

use AppBundle\Entity\User;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;


class RegistrationType extends EditType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'user.email'
            ]);
        switch ($options['captcha_type']) {
            case 'gregwar':
                $builder->add('captcha', CaptchaType::class, [
                    'label' => 'captcha'
                ]);
                break;
            case 'recaptcha':
                $builder->add('recaptcha', EWZRecaptchaType::class, [
                    'label' => 'captcha',
                    'mapped'      => false,
                    'constraints' => [new RecaptchaTrue()]
                ]);
                break;
        }
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => User::class,
                'captcha_type' => null,
                'validation_groups' => ['Default', 'Registration']
            ]
        );
    }
}
