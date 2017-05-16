<?php

namespace AppBundle\Form\User;

use AppBundle\Entity\Tag;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class EditType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'user.password.first'],
                'second_options' => ['label' => 'user.password.second'],
            ]);

        $builder->add('tags', Select2EntityType::class, [
            'multiple' => true,
            'class' => Tag::class,
            'text_property' => 'name',
            'remote_route' => 'tag_list',
            'minimum_input_length' => 0,
            'attr' => [
                'data-theme' => 'bootstrap',
                //'style' => 'width:100%' // workaround
            ],
            'placeholder' => 'tags.placeholder',
            'width' => '100%'
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
