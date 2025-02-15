<?php

namespace App\Form;

use App\Entity\Don;
use App\Entity\evenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('donationdate', null, [
                'widget' => 'single_text',
            ])
            ->add('amount')
            ->add('paymentref')
            ->add('message')
            ->add('evenement', EntityType::class, [
                'class' => evenement::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Don::class,
        ]);
    }
}
