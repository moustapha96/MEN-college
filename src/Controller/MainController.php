<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\EmpruntRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
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


    //mettre a jour la photo profil


    #[Route('/admin/profil/update/avatar', name: 'admin_profil_update_avatar', methods: ['GET', 'POST'])]
    public function updateAvatarAdmin(Request $request, EntityManagerInterface $entityManager)
    {
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

        return $this->render('admin/profil/profil.html.twig', [
            'formAvatar' => $formAvatar->createView(),
            'titre' => 'Mise a jour Profil',
            'user' => $user
        ]);
    }

    #[Route('/client/profil/update/avatar', name: 'client_profil_update_avatar', methods: ['GET', 'POST'])]
    public function updateAvatarClient(Request $request, EntityManagerInterface $entityManager)
    {
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
        $user = $this->getUser();
        $data = $request->request->all();

        if ($data) {

            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setAdresse($data['adresse']);
            $user->setPhone($data['phone']);
            $user->setSexe($data['sexe']);
            $email = $data['email'];

            $user_email = $userRepository->findBy(['email' => $email]);

            if (strcmp($email, $user->getEmail()) != 0) {
                if (in_array($email, $user_email)) {
                    $this->addFlash('warning', "adresse mail existe deja !! ");
                    $referer = $request->headers->get('referer');
                    return new RedirectResponse($referer);
                } else {
                    $user->setEmail($email);
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();
        }
        $this->addFlash('success', "profil mise a jour !");
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
        $user = $this->getUser();
        $data = $request->request->all();

        if ($data) {

            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setAdresse($data['adresse']);
            $user->setPhone($data['phone']);
            $user->setSexe($data['sexe']);
            $email = $data['email'];

            $user_email = $userRepository->findBy(['email' => $email]);

            if (strcmp($email, $user->getEmail()) != 0) {
                if (in_array($email, $user_email)) {
                    $this->addFlash('warning', "adresse mail existe deja !! ");
                    $referer = $request->headers->get('referer');
                    return new RedirectResponse($referer);
                } else {
                    $user->setEmail($email);
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();
        }
        $this->addFlash('success', "profil mise a jour !");
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
}
