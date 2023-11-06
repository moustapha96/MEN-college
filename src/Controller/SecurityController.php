<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\AccountDisabledException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    private $tokenSI;
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenSI = $tokenStorage;
    }


    #[Route('/security', name: 'app_security')]
    public function index(): Response
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(EntityManagerInterface $entityManager): void
    {

        /** @var User $user */
        $user = $this->getUser();
        $user->setIsActiveNow(false);
        $user->setLastActivityAt(new DateTime());
        $this->tokenSI->setToken(null);
        $entityManager->persist($user);
        $entityManager->flush();
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
