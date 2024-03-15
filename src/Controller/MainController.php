<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

class MainController extends AbstractController
{

    #[Route('/admin/profil', name: 'admin_profil')]
    public function profilAdmin(): Response
    {
        $user = $this->getUser();
        return $this->render('admin/profil/profil.html.twig', [
            'titre' => 'Mise a jour Profil',
            'user' => $user
        ]);
    }


    #[Route('/client/profil', name: 'client_profil')]
    public function profil(): Response
    {
        $user = $this->getUser();
        return $this->render('client/profil/profil.html.twig', [
            'titre' => 'Mise a jour Profil',
            'user' => $user
        ]);
    }

    #[Route('/super_admin/profil', name: 'super_admin_profil')]
    public function profilSA(): Response
    {
        $user = $this->getUser();
        return $this->render('super_admin/profil/profil.html.twig', [
            'titre' => 'Mise a jour Profil',
            'user' => $user
        ]);
    }


    #[Route('/super_admin/profil/save', name: 'super_admin_profil_save_avatar', methods: ['POST'])]
    public function updateAvatarSAdmin(Request $request, EntityManagerInterface $entityManager)
    {
        /** @var User $user */
        $user = $this->getUser();


        $uploadedFile = $request->files->get('avatar');

        if ($uploadedFile) {
            $destination = $this->getParameter('kernel.project_dir') . '/public/avatars/';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $user->setAvatar($newFilename);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "Photo profil mise a jour !");
            return $this->redirectToRoute('super_admin_profil');
        }

        $this->addFlash('warning', "Photo profil non mise a jour !");
        return $this->redirectToRoute('super_admin_profil');
    }

    #[Route('/admin/profil', name: 'admin_profil_update_avatar', methods: ['GET'])]
    public function updateAvatarAdmin()
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render('admin/profil/profil.html.twig', [
            'titre' => 'Mise a jour Profil',
            'user' => $user
        ]);
    }


    #[Route('/admin/profil/save', name: 'admin_profil_save_avatar', methods: ['POST'])]
    public function saveAvatarAdmin(
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        /** @var User $user */
        $user = $this->getUser();

        $uploadedFile = $request->files->get('avatar');

        if ($uploadedFile) {
            $destination = $this->getParameter('kernel.project_dir') . '/public/avatars/';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $user->setAvatar($newFilename);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "Photo profil mise a jour !");
            return $this->redirectToRoute('admin_profil_update_avatar');
        }

        $this->addFlash('warning', "Photo profil non mise a jour !");
        return $this->redirectToRoute('admin_profil_update_avatar');
    }

    #[Route('/client/profil/update/avatar', name: 'client_profil_update_avatar', methods: ['GET', 'POST'])]
    public function updateAvatarClient(Request $request, EntityManagerInterface $entityManager)
    {
        /** @var User $user */
        $user = $this->getUser();
        $defaultData = ['message' => "formulaire de modification du profil"];
        $formAvatar = $this->createFormBuilder($defaultData)
            ->add(
                'avatar',
                FileType::class,
                [
                    'required' => false,
                    'label' => 'photo profil',
                    'constraints' => [
                        new File([
                            'mimeTypes' => [
                                "image/png", "image/jpeg", "image/jpg", "image/gif"
                            ],
                            'maxSize' => '4096k',
                            'mimeTypesMessage' => 'trop volumineuse , veuillez choisir une autre image',

                        ])
                    ]
                ]
            )->add('enregistrer', SubmitType::class, [
                'attr' => ['class' => 'btn btn-outline-success mt-3 '],
                'label' => 'enregistrer'
            ])
            ->getForm();

        $formAvatar->handleRequest($request);
        if ($formAvatar->isSubmitted() && $formAvatar->isValid()) {
            $uploadedFile = $formAvatar['avatar']->getData();
            $destination = $this->getParameter('kernel.project_dir') . '/public/avatars/';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $user->setAvatar($newFilename);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "photo profil mise a jour !");
        }

        return $this->render('client/profil/profil.html.twig', [
            'formAvatar' => $formAvatar->createView(),
            'titre' => 'Mise a jour Profil',
            'user' => $user
        ]);
    }


    //function de mise en jour des donnees
    #[Route('/admin/profil/update', name: 'admin_profil_update_data', methods: ['POST'])]
    public function updateDataAdmin(
        Request                $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        /** @var User $user */
        $user = $this->getUser();
        $data = $request->request->all();

        if ($request->isMethod('POST')) {

            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setAdresse($data['adresse']);
            $user->setSexe($data['sexe']);

            $email = $data['email'];
            $phone = $data['phone'];

            $userWithEmail = $userRepository->findOneBy(['email' => $email]);
            $userWithPhone = $userRepository->findOneBy(['phone' => $phone]);

            if ($userWithEmail && $userWithEmail !== $user) {
                $this->addFlash('warning', "L'adresse email existe déjà !");
                return $this->render('admin/profil/profil.html.twig', [
                    'titre' => 'Mise a jour Profil',
                    'user' => $this->getUser()
                ]);
            } else {
                $user->setEmail($email);
            }

            if ($userWithPhone && $userWithPhone !== $user) {
                $this->addFlash('warning', "Le numéro de téléphone existe déjà !");
                return $this->render('admin/profil/profil.html.twig', [
                    'titre' => 'Mise a jour Profil',
                    'user' => $this->getUser()
                ]);
            } else {
                $user->setPhone($phone);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Profil mis à jour !");
        }

        $user = $this->getUser();
        $defaultData = ['message' => "formulaire de modification du profil"];
        $formAvatar = $this->createFormBuilder($defaultData)
            ->add(
                'avatar',
                FileType::class,
                [
                    'required' => false,
                    'label' => 'photo',
                    'constraints' => [
                        new File([
                            'mimeTypes' => [
                                "image/png", "image/jpeg", "image/jpg", "image/gif"
                            ],
                            'maxSize' => '4096k',
                            'mimeTypesMessage' => 'trop volumineuse , veuillez choisir une autre image',

                        ])
                    ]
                ]
            )->add('update', SubmitType::class, [
                'attr' => ['class' => 'btn btn-outline-success mt-3 ']
            ])
            ->getForm();

        $formAvatar->handleRequest($request);
        return $this->render('admin/profil/profil.html.twig', [
            'formAvatar' => $formAvatar->createView(),
            'titre' => 'Mise a jour Profil',
            'user' => $user
        ]);
    }

    #[Route('/client/profil/update', name: 'client_profil_update_data', methods: ['POST'])]
    public function updateDataClient(
        Request                $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        /** @var User $user */
        $user = $this->getUser();
        $data = $request->request->all();
        if ($request->isMethod('POST')) {

            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setAdresse($data['adresse']);
            // $user->setSexe($data['sexe']);

            $email = $data['email'];
            $phone = $data['phone'];

            $userWithEmail = $userRepository->findOneBy(['email' => $email]);
            $userWithPhone = $userRepository->findOneBy(['phone' => $phone]);

            if ($userWithEmail && $userWithEmail !== $user) {
                $this->addFlash('warning', "L'adresse email existe déjà !");
                return $this->render('client/profil/profil.html.twig', [
                    'titre' => 'Mise a jour Profil',
                    'user' => $this->getUser()
                ]);
            } else {
                $user->setEmail($email);
            }

            if ($userWithPhone && $userWithPhone !== $user) {
                $this->addFlash('warning', "Le numéro de téléphone existe déjà !");
                return $this->render('client/profil/profil.html.twig', [
                    'titre' => 'Mise a jour Profil',
                    'user' => $this->getUser()
                ]);
            } else {
                $user->setPhone($phone);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Profil mis à jour !");
        }
        $user = $this->getUser();
        $defaultData = ['message' => "formulaire de modification du profil"];
        $formAvatar = $this->createFormBuilder($defaultData)
            ->add(
                'avatar',
                FileType::class,
                [
                    'required' => false,
                    'label' => 'photo',
                    'constraints' => [
                        new File([
                            'mimeTypes' => [
                                "image/png", "image/jpeg", "image/jpg", "image/gif"
                            ],
                            'maxSize' => '4096k',
                            'mimeTypesMessage' => 'trop volumineuse , veuillez choisir une autre image',

                        ])
                    ]
                ]
            )->add('update', SubmitType::class, [
                'attr' => ['class' => 'btn btn-outline-success mt-3 ']
            ])
            ->getForm();

        $formAvatar->handleRequest($request);
        return $this->render('client/profil/profil.html.twig', [
            'formAvatar' => $formAvatar->createView(),
            'titre' => 'Mise a jour Profil',
            'user' => $user
        ]);
    }

    //function de mise en jour des donnees
    #[Route('/sa/profil/update', name: 'super_admin_profil_update_data', methods: ['POST'])]
    public function updateDataSAdmin(
        Request                $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        /** @var User $user */
        $user = $this->getUser();
        $data = $request->request->all();

        if ($request->isMethod('POST')) {

            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setAdresse($data['adresse']);
            $user->setSexe($data['sexe']);

            $email = $data['email'];
            $phone = $data['phone'];

            $userWithEmail = $userRepository->findOneBy(['email' => $email]);
            $userWithPhone = $userRepository->findOneBy(['phone' => $phone]);

            if ($userWithEmail && $userWithEmail !== $user) {
                $this->addFlash('warning', "L'adresse email existe déjà !");
                return $this->redirectToRoute('super_admin_profil');
            } else {
                $user->setEmail($email);
            }

            if ($userWithPhone && $userWithPhone !== $user) {
                $this->addFlash('warning', "Le numéro de téléphone existe déjà !");
                return $this->redirectToRoute('super_admin_profil');
            } else {
                $user->setPhone($phone);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Profil mis à jour !");
            return $this->redirectToRoute('super_admin_profil');
        }

        $user = $this->getUser();
        $defaultData = ['message' => "formulaire de modification du profil"];
        $formAvatar = $this->createFormBuilder($defaultData)
            ->add(
                'avatar',
                FileType::class,
                [
                    'required' => false,
                    'label' => 'photo',
                    'constraints' => [
                        new File([
                            'mimeTypes' => [
                                "image/png", "image/jpeg", "image/jpg", "image/gif"
                            ],
                            'maxSize' => '4096k',
                            'mimeTypesMessage' => 'trop volumineuse , veuillez choisir une autre image',

                        ])
                    ]
                ]
            )->add('update', SubmitType::class, [
                'attr' => ['class' => 'btn btn-outline-success mt-3 ']
            ])
            ->getForm();

        $formAvatar->handleRequest($request);
        return $this->render('admin/profil/profil.html.twig', [
            'formAvatar' => $formAvatar->createView(),
            'titre' => 'Mise a jour Profil',
            'user' => $user
        ]);
    }
}
