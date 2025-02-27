<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\Partenariat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
<<<<<<< Updated upstream
            ->add('datePostulation', null, [
                'widget' => 'single_text',
            ])
            ->add('status')
            ->add('partenariat', EntityType::class, [
                'class' => Partenariat::class,
                'choice_label' => 'id',
=======
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
>>>>>>> Stashed changes
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
        ]);
    }
}
