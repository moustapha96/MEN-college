<?php

namespace App\Security;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class UserAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    public $tokenSI;
    public $em;
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager
    ) {
        $this->tokenSI = $tokenStorage;
        $this->em = $entityManager;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {


        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        // if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
        //     return new RedirectResponse($targetPath);
        // }

        if (!$token->getRoleNames()[0]) {
            return new RedirectResponse($targetPath);
        }


        /** @var User $user */
        $user = $token->getUser();

        if ($user->getEnabled() == false) {
            throw new AccountDisabledException("Votre compte a été desactivé ");
        } else {

            $user->setIsActiveNow(true);
            $user->setLastActivityAt(new DateTime());
            $this->em->persist($user);
            $this->em->flush();

            if ($user->getEnabled() == true &&  "ROLE_SUPER_ADMIN" === $user->getRoles()[0]) {
                return new RedirectResponse($this->urlGenerator->generate('super_admin_home'));
            } else if ($user->getEnabled() == true &&  "ROLE_ADMIN" === $user->getRoles()[0]) {
                return new RedirectResponse($this->urlGenerator->generate('admin_home'));
            } else if ($user->getEnabled() == true && "ROLE_USER" === $user->getRoles()[0]) {
                return new RedirectResponse($this->urlGenerator->generate('client_home'));
            }
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function logout()
    {
        // Invalidate the current user's authentication token
        $this->tokenSI->setToken(null);
    }
}
