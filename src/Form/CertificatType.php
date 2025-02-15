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
            ->add('nom')
            ->add('dateobt', null, [
                'widget' => 'single_text',
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
