<?php

namespace App\Form;

use App\Entity\Don;
use App\Entity\Evenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints as Assert;

class DonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', NumberType::class, [
                'label' => 'Montant du don',
                'attr' => [
                    'placeholder' => 'Entrez le montant de votre don',
                    'min' => 1
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le montant est obligatoire'
                    ]),
                    new Assert\Positive([
                        'message' => 'Le montant doit être positif'
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'Le montant doit être un nombre'
                    ]),
                    new Assert\GreaterThan([
                        'value' => 0,
                        'message' => 'Le montant minimum est de 1€'
                    ])
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Laissez un message de soutien...',
                    'rows' => 3
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 500,
                        'maxMessage' => 'Votre message ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('paymentref', HiddenType::class, [
                'data' => 'REF-'.uniqid()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Don::class,
            'validation_groups' => ['Default']
        ]);
        
        $resolver->setDefined(['evenement']);
    }
}
