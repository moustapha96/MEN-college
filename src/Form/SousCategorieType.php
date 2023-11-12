<?php

namespace App\Form;

use App\Entity\College;
use App\Entity\SousCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SousCategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control m-2'],
                'disabled' => true,
                'label' => 'Nom',
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control m-2'],

                'label' => 'Description',
            ])
            ->add('college', EntityType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control m-2'],
                'class' => College::class,
                'choice_label' => function ($college) {
                    return $college->getNom();
                },
                'label' => 'College',
                'placeholder' => 'Sélectionnez un collège',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SousCategorie::class,
        ]);
    }
}
