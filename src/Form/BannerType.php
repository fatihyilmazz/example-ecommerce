<?php

namespace App\Form;

use App\Entity\Banner;
use App\Entity\Sector;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class BannerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 255,
                    ]),
                ]
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ]
            ])
            ->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('bannerType', EntityType::class, [
                'class' => \App\Entity\BannerType::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('imageUrl', FileType::class, [
                'data_class' => null,
                'constraints' => [
                    new NotBlank(),
                    new Image([
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/webp'],
                    ]),
                ]
            ])
            ->add('actionUrl', UrlType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 255,
                    ]),
                ]
            ])
            ->add('startedAt', DateTimeType::class, [
                'input' => 'string',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'constraints' => [
                    new DateTime(),
                ]
            ])
            ->add('finishedAt', DateTimeType::class, [
                'input' => 'string',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'constraints' => [
                    new DateTime(),
                    new GreaterThan([
                        'propertyPath' => 'parent.all[startedAt].data',
                        'message' => 'Bu değer başlangıç tarihinden daha ileri bir tarih olmalıdır.'
                    ])
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'false_values' => [
                    'false',
                    '0',
                ],
                'empty_data' => '0',
            ]);

        $builder->get('startedAt')->addModelTransformer(new CallbackTransformer(
            function ($originalValue) {
                if ($originalValue instanceof \DateTimeInterface) {
                    return $originalValue->format('Y-m-d H:i:s');
                }

                return $originalValue;
            },
            function ($submittedValue) {
                if (empty($submittedValue)) {
                    return null;
                }

                return new \DateTime($submittedValue);
            }
        ));

        $builder->get('finishedAt')->addModelTransformer(new CallbackTransformer(
            function ($originalValue) {
                if ($originalValue instanceof \DateTimeInterface) {
                    return $originalValue->format('Y-m-d H:i:s');
                }

                return $originalValue;
            },
            function ($submittedValue) {
                if (empty($submittedValue)) {
                    return null;
                }

                return new \DateTime($submittedValue);
            }
        ));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (array_key_exists('imageExist', $data) && ($data['imageExist'] == 1) && empty($data['imageUrl'])) {
                $form->add('imageUrl', TextType::class, [
                    'data' => $form->getNormData()->getImageUrl(),
                    'empty_data' => $form->getNormData()->getImageUrl(),
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
            'data_class' => Banner::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'banners_token',
            'allow_file_upload' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
