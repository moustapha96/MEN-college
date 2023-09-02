<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matricule', TextType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control '],
                'label' => 'Matricule',
            ])
            ->add('email', TextType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control '],
                'label' => 'Email',
            ])
            ->add('firstName', TextType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control '],
                'label' => 'Prenom',
            ])
            ->add('lastName', TextType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control '],
                'label' => 'Nom',
            ])
            ->add('phone', TextType::class, [
                'attr' => [
                    'class' => 'col-6 mx-auto form-control',
                    'pattern' => '^(76|77|78|75|33|88)\d{7}$',
                ],
                'label' => 'Téléphone',
            ])
            ->add('adresse', TextType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control '],
                'label' => 'Adresse',
            ])
            ->add('sexe', ChoiceType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control '],
                'label' => 'Sexe',
                'choices' => [
                    'Masculin' => 'MASCULIN',
                    'Féminin' => 'FÉMININ',
                ],
                'placeholder' => 'Sélectionnez le sexe',
                // Vous pouvez également ajouter d'autres options comme 'required' ici
            ])
            // ->add('roles', ChoiceType::class, [
            //     'attr' => ['class' => 'col-6 mx-auto form-control '],
            //     'label' => 'Sexe',
            //     'choices' => [
            //         'ADMIN' => 'ROLE_ADMIN',
            //         'USER' => 'ROLE_USER',
            //     ],
            //     'placeholder' => 'Sélectionnez le profil',
            //     // Vous pouvez également ajouter d'autres options comme 'required' ici
            // ])
            ->add('valider', SubmitType::class, [
                'label' => '<i class="fa fa-save"></i> Valider', // Utilisez l'icône de Font Awesome ici
                'label_html' => true, // Permettre l'utilisation de HTML dans le label
                'attr' => ['class' => 'btn btn-outline-success mt-3  ']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
