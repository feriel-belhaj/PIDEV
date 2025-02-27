<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rolesChoices = [
            'Client' => 'ROLE_CLIENT',
            'Artisan' => 'ROLE_ARTISAN',
        ];
        if ($options['is_back_office']) {
            $rolesChoices['Admin'] = 'ROLE_ADMIN';
        }
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
            ])
            ->add('adresse')
            ->add('telephone')
            
            ->add('imageFile', FileType::class, [
                'label' => 'Image de profil',
                'mapped' => false, 
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
                    ]),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => $rolesChoices,
                'attr' => [
                    'class' => 'custom-select',
                    'placeholder' => 'Rôle'],
                'label' => false
            ])
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'male',
                    'Femme' => 'femelle',
                    
                ],
                'expanded' => true, 
                'multiple' => false, 
                'label' => false, 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'is_back_office' => false,
        ]);
    }
}
