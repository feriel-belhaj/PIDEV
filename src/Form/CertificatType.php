<?php

namespace App\Form;

use App\Entity\Certificat;
use App\Entity\Formation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CertificatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ])
                ],
            ])
            ->add('prenom', null, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères.',
                    ])
                ],
            ])
            ->add('dateobt', null, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date d\'obtention est obligatoire.']),
                    new Assert\Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'La date n\'est pas valide.'
                    ])
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
            ->add('nomorganisme', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'organisme est obligatoire.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le nom de l\'organisme doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom de l\'organisme ne peut pas dépasser {{ limit }} caractères.',
                    ])
                ]
            ])
            ->add('formation', EntityType::class, [
                'class' => Formation::class,
                'choice_label' => 'titre',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La formation est obligatoire.'])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn-submit']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Certificat::class,
        ]);
    }
}
