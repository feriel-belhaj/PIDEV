<?php

namespace App\Form;

use App\Entity\Certificat;
use App\Entity\formation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
<<<<<<< Updated upstream
            ->add('nom')
            ->add('dateobt', null, [
                'widget' => 'single_text',
=======
            ->add('nom', null, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\'-]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres'
                    ])
                ],
            ])
            ->add('prenom', null, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\'-]+$/',
                        'message' => 'Le prénom ne doit contenir que des lettres'
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
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date ne peut pas être dans le futur.'
                    ])
                ]
            ])
            ->add('niveau', ChoiceType::class, [
                'choices' => [
                    'Sélectionnez un niveau' => '',
                    'Débutant' => 'debutant',
                    'Intermédiaire' => 'intermediaire',
                    'Avancé' => 'avance',
                ],
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
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\'-]+$/',
                        'message' => 'Le nom de l\'organisme ne doit contenir que des lettres'
                    ])
                ]
>>>>>>> Stashed changes
            ])
            ->add('niveau')
            ->add('nomorganisme')
            ->add('formation', EntityType::class, [
                'class' => formation::class,
                'choice_label' => 'id',
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
