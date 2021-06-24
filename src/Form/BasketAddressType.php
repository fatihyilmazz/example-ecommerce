<?php

namespace App\Form;

use App\Entity\Basket;
use App\Entity\Address;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class BasketAddressType extends AbstractType
{
    public $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shippingAddress', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'id',
            ])
            ->add('billingAddress', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('addressType', CheckboxType::class, [
                'mapped' => false,
              /*  'false_values' => [
                    'false',
                    '0',
                ],*/
                'empty_data' => '0',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Basket::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'basket_address_token',
            'allow_file_upload' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
