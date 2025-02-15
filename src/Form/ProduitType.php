<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nom', null, [
            'constraints' => [
                new Assert\NotBlank(message: "Le nom du produit est obligatoire."),
                new Assert\Length(['min' => 3, 'max' => 100, 'minMessage' => "Le nom doit contenir au moins {{ limit }} caractères."]),
            ],
        ])
        ->add('description', null, [
            'constraints' => [
                new Assert\NotBlank(message: "La description ne peut pas être vide."),
            ],
        ])
        ->add('prix', null, [
            'constraints' => [
                new Assert\NotBlank(message: "Le prix est obligatoire."),
                new Assert\Positive(message: "Le prix doit être un nombre positif."), // Erreur classique pour un prix négatif
                new Assert\Callback([$this, 'validatePrix']), // Validation personnalisée
            ],
        ])
        ->add('quantitestock', null, [
            'constraints' => [
                new Assert\NotBlank(message: "La quantité en stock est obligatoire."),
                new Assert\PositiveOrZero(message: "La quantité ne peut pas être négative."),
            ],
        ])
        ->add('categorie', null, [
            'constraints' => [
                new Assert\NotBlank(message: "Veuillez choisir une catégorie."),
            ],
        ])
        ->add('datecreation', null, [
            'widget' => 'single_text',
            'constraints' => [
                new Assert\NotBlank(message: "La date de création est obligatoire."),
                new Assert\LessThanOrEqual([
                    "value" => "today",
                    "message" => "La date de création ne peut pas être supérieure à la date d'aujourd'hui."
                ]),
            ],
        ])
        ->add('image', FileType::class, [
            'label' => 'Image du produit',
            'mapped' => false, // Ne mappe pas directement sur l'entité
            'required' => true, // Rendre l'image obligatoire
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Veuillez télécharger une image.'
                ]),
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, WEBP).',
                ]),
            ],
            'attr' => ['class' => 'form-control']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
    ////
    public function validatePrix($value, ExecutionContextInterface $context): void
{
    if ($value < 0) {
        $context->buildViolation("La valeur {{ value }} est négative.")
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
}
