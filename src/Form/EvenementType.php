<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du projet',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Donnez un titre accrocheur à votre projet'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le titre est obligatoire'
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[a-zA-Z]/',
                        'message' => 'Le titre doit contenir au moins une lettre'
                    ]),
                    new Assert\NotCompromisedPassword([
                        'message' => 'Le titre ne peut pas être composé uniquement de chiffres'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Décrivez votre projet en détail'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La description est obligatoire'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('startdate', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'data' => new \DateTime(),
                'constraints' => [
                    new Assert\Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'La date n\'est pas valide'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de début doit être égale ou ultérieure à aujourd\'hui'
                    ])
                ]
            ])
            ->add('enddate', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'data' => (new \DateTime())->modify('+1 day'),
                'constraints' => [
                    new Assert\Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'La date n\'est pas valide'
                    ]),
                    new Assert\GreaterThan([
                        'propertyPath' => 'parent.all[startdate].data',
                        'message' => 'La date de fin doit être postérieure à la date de début'
                    ])
                ]
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'attr' => ['placeholder' => 'Où se déroulera votre projet ?'],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La localisation est obligatoire'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'La localisation doit contenir au moins {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('goalamount', NumberType::class, [
                'label' => 'Objectif de financement',
                'attr' => [
                    'placeholder' => 'Montant souhaité',
                    'min' => 0
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'objectif de financement est obligatoire'
                    ]),
                    new Assert\Positive([
                        'message' => 'Le montant doit être positif'
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'Le montant doit être un nombre'
                    ]),
                    new Assert\GreaterThan([
                        'value' => 100,
                        'message' => 'L\'objectif minimum est de 100€'
                    ])
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du projet',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Une image est obligatoire'
                    ]),
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF)',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser {{ limit }}',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
            'validation_groups' => ['Default'],
        ]);
    }
}
