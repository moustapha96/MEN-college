<?php

namespace App\Controller;

use App\Security\AccountDisabledException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,

    ) {
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

        /** @var User $user */
        $user = $this->getUser();
        if ($user) {

            if ($user->getEnabled() == false) {
                throw new AccountDisabledException("votre compte a été desactivé  depuis votre dernier connexion");

                return new RedirectResponse($this->urlGenerator->generate('app_logout'));
            } else if ($user->getEnabled() == true &&  "ROLE_ADMIN" === $user->getRoles()[0]) {
                return new RedirectResponse($this->urlGenerator->generate('admin_home'));
            } else if ($user->getEnabled() == true && "ROLE_USER" === $user->getRoles()[0]) {
                return new RedirectResponse($this->urlGenerator->generate('client_home'));
            }
        }

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
        $entityManager->persist($user);
        $entityManager->flush();
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
