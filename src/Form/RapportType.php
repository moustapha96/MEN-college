<?php

namespace App\Form;

use App\Entity\College;
use App\Entity\Rapport;
use App\Entity\User;
use App\Repository\CollegeRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\File;

class RapportType extends AbstractType
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        /** @var User $user  */
        $user = $this->security->getUser();

        $builder

            ->add('college', EntityType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control m-2'],
                'class' => College::class,
                'choice_label' => 'nom', // Supposant que 'nom' est le champ que vous souhaitez afficher dans la liste déroulante
                'label' => 'Collège',
                'placeholder' => 'Sélectionnez un collège',
                'query_builder' => function (CollegeRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->andWhere('c.nom = :user_college')
                        ->setParameter('user_college', $user->getCollege()->getNom());
                },
            ])
            ->add('activite', TextareaType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control ', 'row' => 3],
                'label' => 'Activité',
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control ', 'row' => 3],
                'label' => 'Déscription',
            ])
            ->add('resultat', TextareaType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control ', 'row' => 3],
                'label' => 'Résultats',
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



            ->add('statut', ChoiceType::class, [
                'attr' => ['class' => 'col-6 mx-auto form-control'],
                'label' => 'Statut Rapport',
                'disabled' => true,
                'choices' => [
                    'EN ATTENTE' => 'EN ATTENTE', // Vous pouvez utiliser des valeurs plus explicites si nécessaire
                    'VALIDER' => 'VALIDER',
                    'NON VALIDER' => 'NON VALIDER',
                ],
                'placeholder' => 'Sélectionnez une option', // Optionnel, crée un champ vide par défaut
            ])

            // ->add('valider', SubmitType::class, ['attr' => ['class' => 'btn btn-outline-success']]);
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
