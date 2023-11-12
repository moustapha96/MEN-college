<?php

namespace App\Form;

use App\Entity\Rapport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;


class RapportEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('activite', TextareaType::class, [
            'attr' => ['class' => 'col-6 mx-auto form-control ', 'row' => 3],
            'label' => 'activite',
        ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control ', 'row' => 3],
                'label' => 'description',
            ])
            ->add('resultat', TextareaType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control ', 'row' => 3],
                'label' => 'resultats',
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
