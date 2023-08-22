<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
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
                'attr' => ['class' => 'col-6 mx-auto form-control '],
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
            ])

            // ->add('plainPassword', PasswordType::class, [
            //     'mapped' => false,
            //     'label' => 'Mot de Passe',
            //     'attr' => ['class' => 'col-6 mx-auto form-control ', 'autocomplete' => 'new-password'],
            //     'constraints' => [
            //         new NotBlank([
            //             'message' => 'Please enter a password',
            //         ]),
            //         new Length([
            //             'min' => 6,
            //             'minMessage' => 'Your password should be at least {{ limit }} characters',
            //             // max length allowed by Symfony for security reasons
            //             'max' => 4096,
            //         ]),
            //     ],
            // ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,

                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['autocomplete' => 'new-password', 'class' => 'col-6 mx-auto form-control']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de Passe'],
                'second_options' => ['label' => 'Répéter Mot de Passe'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            // ->add('agreeTerms', CheckboxType::class, [
            //     'attr' => ['class' => 'mx-auto'],
            //     'mapped' => false,
            //     'constraints' => [
            //         new IsTrue([
            //             'message' => 'You should agree to our terms.',
            //         ]),
            //     ],
            // ])

            ->add('valider', SubmitType::class, [
                'label' => '<i class="fa fa-save"></i> Valider', // Utilisez l'icône de Font Awesome ici
                'label_html' => true, // Permettre l'utilisation de HTML dans le label
                'attr' => ['class' => 'btn btn-outline-success']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
