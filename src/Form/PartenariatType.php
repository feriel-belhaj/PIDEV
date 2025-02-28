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
            'label' => 'Nom',
            'required' => true ,
            'attr' => ['placeholder' => 'Entrez le nom du partenariat']
        ])
        ->add('type', TextType::class, [
            'label' => 'Type',
            'attr' => ['placeholder' => 'Entrez le type du partenariat']
        ])
        ->add('description', TextareaType::class, [
            'attr' => [
                'class' => 'form-control mb-3 rounded',
                'placeholder' => 'Description du Partenariat',
                'required' => true 
            ]
        ])
       
        ->add('statut', ChoiceType::class, [
            'choices'  => [
                'Actif' => 'actif',
                'En cours' => 'en cours',
                'Expiré' => 'expiré',
            ],
            'required' => !$options['is_edit'],
            'placeholder' => 'Choisir le statut',
            'attr' => ['class' => 'form-control mb-3 rounded-pill'],
            'empty_data' => $options['is_edit'] ? '' : 'actif',
            'constraints' => !$options['is_edit'] ? [
                new Assert\NotBlank(['message' => 'Le statut est obligatoire.'])
            ] : [], 
        ])
       
       

        ->add('dateDebut', DateType::class, [
            'label' => 'Date de début',
            'widget' => 'single_text'
        ])
        ->add('dateFin', DateType::class, [
            'label' => 'Date de fin',
            'widget' => 'single_text'
        ])
           
           
        ->add('image', FileType::class, [
            'label' => 'Image du Partenariat',
            'mapped' => false, 
            'required' => !$options['is_edit'], 
            'attr' => ['class' => 'form-control mb-3 rounded-pill'],
            'constraints' => [
                new Assert\File([
                    'maxSize' => '2M', 
                    'mimeTypes' => ['image/png', 'image/jpeg', 'image/jpg'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image au format PNG, JPEG ou JPG.',
                ]),
            ],
        ]);
       
    }
   


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Partenariat::class,
            'is_edit' => false, 
            'csrf_protection' => true

        ]);
    }
}


