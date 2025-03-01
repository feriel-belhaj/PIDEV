<?php

namespace App\Form;

use App\Entity\Commentaire;
use App\Entity\Creation;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                    'Signalé' => 'signalé'
                ],
                'required' => true
            ])
            ->add('creation', EntityType::class, [
                'class' => Creation::class,
                'choice_label' => 'titre',
                'required' => true
            ])
        ;
        
        // Only add the utilisateur field if it's an admin form
        if ($options['include_user_field']) {
            $builder->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function(Utilisateur $utilisateur) {
                    return $utilisateur->getNom() . ' ' . $utilisateur->getPrenom();
                },
                'required' => true
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
            'include_user_field' => false, // By default, don't include the user field
        ]);
    }
}
