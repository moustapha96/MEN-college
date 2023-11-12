<?php

namespace App\Form;

use App\Entity\Rapport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\File;

class RapportTypeEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('activite', TextType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control '],
                'label' => 'activite',
            ])
            ->add('description', TextType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control '],
                'label' => 'description',
            ])
            ->add('resultat', TextType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control '],
                'label' => 'resultats',
            ])

            ->add('fichier', FileType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control'],
                'label' => 'Fichiers',
                'label_attr' => ['class' => 'col-6 mx-auto'], // Style pour le libellé
                'multiple' => true, // Permet de sélectionner plusieurs fichiers
                'required' => false, // Facultatif, si les fichiers ne sont pas obligatoires
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/pdf', // Types MIME autorisés pour les fichiers PDF
                        ],
                        'maxSize' => '4096k', // Taille maximale autorisée (4 Mo dans cet exemple)
                        'mimeTypesMessage' => 'Veuillez choisir un fichier PDF.', // Message d'erreur personnalisé
                    ]),
                ],
            ])


            ->add('valider', SubmitType::class, [
                'label' => '<i class="fa fa-save"></i> Valider', // Utilisez l'icône de Font Awesome ici
                'label_html' => true, // Permettre l'utilisation de HTML dans le label
                'attr' => ['class' => 'btn btn-outline-success']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rapport::class,
        ]);
    }
}
