<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('startdate', null, [
                'widget' => 'single_text',
            ])
            ->add('enddate', null, [
                'widget' => 'single_text',
            ])
            ->add('localisation')
            ->add('goalamount')
            ->add('collectedamount')
            ->add('status')
            ->add('imageurl')
            ->add('createdat', null, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
