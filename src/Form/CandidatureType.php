<?php

namespace App\Form;

use App\Entity\Candidature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Détermine si on est en mode édition ou ajout
        $isEdit = $options['is_edit'] ?? false;

        $builder
        ->add('datePostulation', DateType::class, [
            'widget' => 'single_text',
            'attr' => ['readonly' => true], 
        ])
            ->add('typeCollab', ChoiceType::class, [
                'choices' => [
                    'Stage' => 'Stage',
                    'Sponsoring' => 'Sponsoring',
                    'Atelier collaboratif' => 'Atelier collaboratif',
                ],
                'placeholder' => 'Choisir le Type de Collaboration',
                'attr' => ['class' => 'form-select border-primary p-2'],
                'required' => !$isEdit,
                'constraints' => !$isEdit ? [
                    new Assert\NotBlank(['message' => 'remplir champs']),
                ] : [],
            ])
            ->add('cv', FileType::class, [
                'required' => !$isEdit,
                'mapped' => false,
                'constraints' => array_merge(
                    [
                        new Assert\File([
                            'maxSize' => '5M',
                            'mimeTypes' => ['application/pdf'],
                            'mimeTypesMessage' => 'Le CV doit être un fichier PDF.',
                        ]),
                    ],
                    !$isEdit ? [
                        new Assert\NotBlank(['message' => 'remplir champs']),
                    ] : []
                ),
            ])
            ->add('portfolio', FileType::class, [
                'required' => !$isEdit,
                'mapped' => false,
                'constraints' => array_merge(
                    [
                        new Assert\File([
                            'maxSize' => '5M',
                            'mimeTypes' => ['image/png', 'image/jpeg', 'image/jpg'],
                            'mimeTypesMessage' => 'Le portfolio doit être une image PNG, JPEG ou JPG.',
                        ]),
                    ],
                    !$isEdit ? [
                        new Assert\NotBlank(['message' => 'remplir champs']),
                    ] : []
                ),
            ])
            ->add('motivation', TextareaType::class, [
                'required' => !$isEdit,
                'constraints' => !$isEdit ? [
                    new Assert\NotBlank(['message' => 'remplir champs']),
                ] : [],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
            'is_edit' => false, // Par défaut, le formulaire est en mode ajout
        ]);
    }
}
