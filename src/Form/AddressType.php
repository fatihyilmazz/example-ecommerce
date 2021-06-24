<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\County;
use App\Entity\Address;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 255,
                    ]),
                ]
            ])
            ->add('zipCode', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 20,
                    ]),
                ]
            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('county', EntityType::class, [
                'class' => County::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(),
                ]
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (array_key_exists('title', $data)) {
                $form->add('title', TextType::class, [
                    'constraints' => [
                        new NotBlank(),
                        new Length([
                            'min' => 5,
                            'max' => 100,
                        ]),
                    ]
                ]);
            }

            if (array_key_exists('contactName', $data)) {
                $form->add('contactName', TextType::class, [
                    'constraints' => [
                        new NotBlank(),
                        new Length([
                            'min' => 5,
                            'max' => 100,
                        ]),
                    ]
                ]);
            }

            if (array_key_exists('phoneNumber', $data)) {
                $form->add('phoneNumber', TextType::class, [
                    'constraints' => [
                        new NotBlank(),
                        new Length([
                            'max' => 50,
                        ]),
                    ]
                ]);
            }
            if (array_key_exists('addressType', $data)) {
                $form->add('addressType', EntityType::class, [
                        'class' => \App\Entity\AddressType::class,
                        'choice_label' => 'id',
                        'constraints' => [
                            new NotBlank(),
                        ]
                ]);
            }
        });
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'address_token',
            'allow_file_upload' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
