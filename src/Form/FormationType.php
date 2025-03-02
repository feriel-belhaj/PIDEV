<?php

namespace App\Form;

use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formation = $builder->getData();
        
        $builder
            ->add('titre', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le titre ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères.',
                    ])
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 5],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 1000,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ])
                ],
            ])
            ->add('datedeb', null, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de début est obligatoire.']),
                    new Assert\Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'La date n\'est pas valide.'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de début ne peut pas être dans le passé.'
                    ])
                ]
            ])
            ->add('datefin', null, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de fin est obligatoire.']),
                    new Assert\Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'La date n\'est pas valide.'
                    ]),
                    new Assert\Callback(function($object, $context) {
                        if (!$object instanceof \DateTimeInterface) {
                            return;
                        }
                        
                        $form = $context->getRoot();
                        $datedeb = $form->get('datedeb')->getData();
                        
                        if ($datedeb instanceof \DateTimeInterface && $object < $datedeb) {
                            $context->buildViolation('La date de fin doit être postérieure ou égale à la date de début.')
                                ->addViolation();
                        }
                    })
                ]
            ])
            ->add('niveau', ChoiceType::class, [
                'choices' => [
                    'Sélectionnez un niveau' => '',  // Option par défaut
                    'Débutant' => 'debutant',
                    'Intermédiaire' => 'intermediaire',
                    'Avancé' => 'avance',
                ],
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un niveau.']),
                    new Assert\Choice([
                        'choices' => ['debutant', 'intermediaire', 'avance'],
                        'message' => 'Veuillez sélectionner un niveau valide.'
                    ])
                ]
            ])
            ->add('prix', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix est obligatoire.']),
                    new Assert\Positive(['message' => 'Le prix doit être un nombre positif.']),
                    new Assert\NotEqualTo([
                        'value' => 0,
                        'message' => 'Le prix ne peut pas être égal à zéro.'
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'Le prix doit être un nombre.'
                    ])
                ]
            ])
            ->add('emplacement', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => "L'emplacement est obligatoire."]),
                ]
            ])
            ->add('nbplace', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => "Le nombre de places est obligatoire."]),
                    new Assert\Positive(['message' => "Le nombre de places doit être un nombre positif."]),
                ]
            ])
            ->add('nbparticipant', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => "Le nombre de participants est obligatoire."]),
                    new Assert\PositiveOrZero(['message' => "Le nombre de participants ne peut pas être négatif."]),
                    new Assert\Callback(function($value, $context) {
                        $form = $context->getRoot();
                        $nbplace = $form->get('nbplace')->getData();
                        
                        if ($nbplace !== null && $value > $nbplace) {
                            $context->buildViolation('Le nombre de participants ne peut pas dépasser le nombre de places disponibles.')
                                ->addViolation();
                        }
                    })
                ]
            ])
            ->add('organisateur', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => "Le nom de l'organisateur est obligatoire."]),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => "Le nom de l'organisateur doit contenir au moins {{ limit }} caractères.",
                        'max' => 255,
                        'maxMessage' => "Le nom de l'organisateur ne peut pas dépasser {{ limit }} caractères."
                    ])
                ]
            ])
            ->add('duree', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => "La durée est obligatoire."]),
                    new Assert\Positive(['message' => "La durée doit être un nombre positif."]),
                    new Assert\NotEqualTo([
                        'value' => 0,
                        'message' => 'La durée ne peut pas être égale à zéro.'
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'La durée doit être un nombre.'
                    ])
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de la formation',
                'mapped' => false,
                'required' => false,
                'data_class' => null,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner une image'
                    ]),
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, GIF)',
                    ])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
