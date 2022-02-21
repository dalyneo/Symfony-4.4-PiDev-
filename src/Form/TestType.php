<?php

namespace App\Form;

use App\Entity\Certificat;
use App\Entity\Test;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('q1')
            ->add('r1')
            ->add('q2')
            ->add('r2')
            ->add('q3')
            ->add('r3')
            ->add('q4')
            ->add('r4')
            ->add('q5')
            ->add('r5')
            //->add('idrecruteur')
            ->add('certificat' , EntityType::class,[
                'class'=>Certificat::class,
                'choice_label'=>'nom',

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Test::class,
        ]);
    }
}
