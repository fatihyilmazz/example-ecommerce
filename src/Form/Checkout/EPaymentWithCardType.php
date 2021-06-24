<?php

namespace App\Form\Checkout;

use App\DTO\Checkout\EPaymentWithCardDTO;
use App\Entity\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Type;

class EPaymentWithCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cardNumber', TextType::class, [
                'constraints' => [
                    new NotBlank(),
//                    new CardScheme([
//                        'schemes' => ['VISA', 'AMEX', 'MASTERCARD', 'MAESTRO'],
//                    ]),
                ]
            ])
            ->add('cardHolderName', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 100,
                    ]),
                ]
            ])
            ->add('cardExpireYear', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new DateTime(['format' => 'Y']),
                    new Length([
                        'min' => 4,
                        'max' => 4,
                    ]),
                ]
            ])
            ->add('cardExpireMonth', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new DateTime(['format' => 'm']),
                    new Length([
                        'min' => 2,
                        'max' => 2
                    ]),
                ]
            ])
            ->add('cardCvc', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 3,
                        'max' => 4
                    ]),
                ]
            ])
            ->add('installment', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 1,
                        'max' => 18
                    ]),
                ]
            ])
            ->add('amount', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Type([
                        'type' => 'numeric',
                    ])
                ]
            ])
            ->add('currencyId', ChoiceType::class, [
                'choices' => [
                    Currency::ID_TRY => Currency::ID_TRY,
                    Currency::ID_USD => Currency::ID_USD,
                    Currency::ID_EUR => Currency::ID_EUR,
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('acceptAgreement', CheckboxType::class, [
                'constraints' => [
                    new IsTrue(),
                ],
                'false_values' => [
                    'false',
                    '0',
                ],
                'empty_data' => '0',
            ])
            // TODO: Arayüzden silindi backend durumu netleştirilecek
            ->add('registerCard', CheckboxType::class, [
                'false_values' => [
                    'false',
                    '0',
                ],
                'empty_data' => '0',
            ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EPaymentWithCardDTO::class,
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'epayment_with_card_token',
            'allow_extra_fields' => true,
        ]);
    }
}
