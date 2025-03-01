<?php

namespace App\Form;

use App\Entity\Creation;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Security;

class CreationType extends AbstractType
{
    private $security;
    
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre ne peut pas être vide',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre de la création'
                ],
                'label' => 'Titre'
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description ne peut pas être vide',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez votre création'
                ],
                'label' => 'Description'
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, GIF)',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est {{ limit }} {{ suffix }}.',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/png,image/jpeg,image/gif'
                ]
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function(Utilisateur $utilisateur) {
                    return $utilisateur->getNom() . ' ' . $utilisateur->getPrenom();
                },
                'required' => true,
                'data' => $this->security->getUser(),
                'disabled' => !$this->security->isGranted('ROLE_ADMIN')
            ])
            ->add('categorie', ChoiceType::class, [
                'choices' => [
                    'Art Digital' => 'art_digital',
                    'Peinture' => 'peinture',
                    'Sculpture' => 'sculpture',
                    'Photographie' => 'photographie',
                    'Artisanat' => 'artisanat',
                    'Autre' => 'autre'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La catégorie est requise',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Catégorie'
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                    'En attente' => 'en_attente'
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Statut'
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            
            // If it's a new creation and no image was uploaded
            if (!isset($data['image']) || empty($data['image'])) {
                $form->remove('image');
                $form->add('image', FileType::class, [
                    'label' => 'Image',
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/jpg',
                                'image/png',
                                'image/gif',
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, GIF)',
                        ])
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'accept' => 'image/png,image/jpeg,image/gif'
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Creation::class,
            'attr' => [
                'class' => 'needs-validation',
                'novalidate' => 'novalidate',
                'enctype' => 'multipart/form-data'
            ]
        ]);
    }
}
