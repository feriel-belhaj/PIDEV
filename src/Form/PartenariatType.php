<?php

namespace App\Form;

use App\Entity\Partenariat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;

class PartenariatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEditMode = $options['is_edit']; // Récupérer la variable is_edit passée à l'option

        $builder
        ->add('nom', TextType::class, [
            'attr' => ['class' => 'form-control mb-3 rounded-pill', 'placeholder' => 'Nom du Partenariat'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'remplir champs.']),
                new Assert\Length([
                    'max' => 50,
                    'maxMessage' => 'Le nom ne peut pas dépasser 50 caractères.'
                ]),
                new Assert\Regex([
                    'pattern' => '/^[A-ZÀ-ÿ][a-zA-ZÀ-ÿéèêëàâäçôùùîï ]+$/',
                    'message' => 'Le nom doit commencer par une majuscule et contenir uniquement des lettres accentuées et des espaces.',
                ]),
            ],
        ])
        
        ->add('type', TextType::class, [
            'attr' => ['class' => 'form-control mb-3 rounded-pill', 'placeholder' => 'Type de Partenariat'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'remplir champs.']),
                new Assert\Length([
                    'max' => 50,
                    'maxMessage' => 'Le type ne peut pas dépasser 50 caractères.'
                ]),
                new Assert\Regex([
                    'pattern' => '/^[A-ZÀ-ÿ][a-zA-ZÀ-ÿéèêëàâäçôùùîï ]+$/',
                    'message' => 'Le type doit commencer par une majuscule et contenir uniquement des lettres accentuées et des espaces.',
                ]),
            ],
        ])
        
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3 rounded', 'placeholder' => 'Description du Partenariat'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'remplir champs.']),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'La description doit contenir au moins 10 caractères.',
                        'max' => 255,
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-ZÀ-ÿ][a-zA-ZÀ-ÿéèêëàâäçôùùîï ,;.:\'"!?-]*$/',
                        'message' => 'La description doit commencer par une majuscule et contenir uniquement des lettres accentuées, des espaces et des signes de ponctuation.',
                    ]),
                ],
            ])
            
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control mb-3 rounded-pill'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de début est obligatoire.']),
                    new Assert\Type([
                        'type' => '\DateTimeInterface',
                        'message' => 'Le format de la date de début est invalide.',
                    ]),
                ],
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control mb-3 rounded-pill'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de fin est obligatoire.']),
                    new Assert\Type([
                        'type' => '\DateTimeInterface',
                        'message' => 'Le format de la date de fin est invalide.',
                    ]),
                    new Assert\GreaterThan([
                        'propertyPath' => 'parent.all[dateDebut].data',
                        'message' => 'La date de fin doit être supérieure à la date de début.',
                    ]),
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'actif',
                'En cours' => 'en cours',
                'Expiré' => 'expiré',
                ],
                'placeholder' => 'Choisir un statut',  // Assurez-vous qu'il n'y ait pas de valeur vide envoyée
                'required' => !$isEditMode, // Si c'est en mode ajout, rendre obligatoire
                'empty_data' => 'actif', // Définir une valeur par défaut si rien n'est sélectionné
            ])
            
            ->add('image', FileType::class, [
                'label' => 'Image du Partenariat',
                'mapped' => false, // Ne correspond pas directement à une propriété de l'entité
                'required' => $isEditMode ? false : true, // Obligatoire en ajout, facultatif en edit
                'attr' => ['class' => 'form-control mb-3 rounded-pill'],
                'constraints' => $isEditMode ? [] : [
                    new Assert\NotBlank(['message' => 'remplir champs.']),
                    new Assert\File([
                        'maxSize' => '2M', // ✅ Taille max de 2 Mo
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/jpg'], // ✅ Formats autorisés
                        'mimeTypesMessage' => 'Veuillez télécharger une image au format PNG, JPEG ou JPG.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Partenariat::class,
            'is_edit' => false, // Par défaut, c'est un ajout
        ]);
    }
}
