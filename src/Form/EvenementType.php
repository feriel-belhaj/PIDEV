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
<<<<<<< Updated upstream
=======
                'data' => new \DateTime(),
                'constraints' => [
                    new Assert\Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'La date n\'est pas valide'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de début doit être égale ou ultérieure à aujourd\'hui'
                    ])
                ]
>>>>>>> Stashed changes
            ])
            ->add('enddate', null, [
                'widget' => 'single_text',
<<<<<<< Updated upstream
=======
                'data' => (new \DateTime())->modify('+1 day'),
                'constraints' => [
                    new Assert\Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'La date n\'est pas valide'
                    ]),
                    new Assert\GreaterThan([
                        'propertyPath' => 'parent.all[startdate].data',
                        'message' => 'La date de fin doit être postérieure à la date de début'
                    ])
                ]
>>>>>>> Stashed changes
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
