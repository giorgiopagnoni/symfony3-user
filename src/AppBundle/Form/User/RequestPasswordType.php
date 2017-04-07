<?php

namespace AppBundle\Form\User;

use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RequestPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', null, [
                'label' => 'user.email',
                'constraints' => [new NotBlank()]
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
                    'mapped' => false,
                    'constraints' => [new RecaptchaTrue()]
                ]);
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['captcha_type' => null]);
    }
}
