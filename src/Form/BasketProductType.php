<?php

namespace App\Form;

use App\Entity\Basket;
use App\Entity\Merchant;
use App\Entity\BasketProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class BasketProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
/*
            ->add('basket', EntityType::class, [
                'class' => Basket::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
*/
            ->add('merchant', EntityType::class, [
                'class' => Merchant::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('productId', NumberType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 10,
                    ]),
                ]
            ])
            ->add('quantity', NumberType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 10,
                    ]),
                    new GreaterThan([
                        'value' => 0,
                    ])
                ]
            ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BasketProduct::class,
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'basket_products_token',
            'allow_extra_fields' => true,
        ]);
    }
}
