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
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control mb-3 rounded-pill', 'placeholder' => 'Nom du Partenariat'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Assert\Length([
                        'max' => 50,
                        'maxMessage' => 'Le nom ne peut pas dépasser 50 caractères.'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z][a-zA-Z]+$/',
                        'message' => 'Le nom doit commencer par une majuscule et contenir uniquement des lettres.'
                    ]),
                ],
            ])
            ->add('type', TextType::class, [
                'attr' => ['class' => 'form-control mb-3 rounded-pill', 'placeholder' => 'Type de Partenariat'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type est obligatoire.']),
                    new Assert\Length([
                        'max' => 50,
                        'maxMessage' => 'Le type ne peut pas dépasser 50 caractères.'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z][a-zA-Z]+$/',
                        'message' => 'Le type doit commencer par une majuscule et contenir uniquement des lettres.'
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3 rounded', 'placeholder' => 'Description du Partenariat'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est obligatoire.']),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'La description doit contenir au moins 10 caractères.',
                        'max' => 255,
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z][a-zA-Z ,;.:\'"!?-]+$/',
                        'message' => 'La description doit commencer par une majuscule et contenir uniquement des lettres, des espaces et des signes de ponctuation.',
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
                'choices'  => [
                    'Actif' => 'actif',
                    'En cours' => 'en cours',
                    'Expiré' => 'expiré',
                ],
                'required' => true,
                'placeholder' => 'Choisir le statut',
                'attr' => ['class' => 'form-control mb-3 rounded-pill'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du Partenariat',
                'mapped' => false, // Ne correspond pas directement à une propriété de l'entité
                'required' => $options['is_edit'] ? false : true, // Obligatoire en ajout, facultatif en edit
                'attr' => ['class' => 'form-control mb-3 rounded-pill'],
                'constraints' => $options['is_edit'] ? [] : [
                    new Assert\NotBlank(['message' => 'L\'image est obligatoire.']),
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
