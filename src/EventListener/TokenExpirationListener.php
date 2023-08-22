<?php


namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class TokenExpirationListener
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $token = $this->tokenStorage->getToken();

        if ($token !== null && $token->isAuthenticated()) {
            if ($this->isTokenExpired($token)) {
                $response = new Response('', Response::HTTP_UNAUTHORIZED);
                $event->setResponse($response);
            }
        }
    }

    private function isTokenExpired($token)
    {
        $expirationTimestamp = $token->getAttributes()['expiration_timestamp'] ?? null;

        if ($expirationTimestamp === null) {
            // If expiration timestamp is not available, consider token as not expired
            return false;
        }

        return $expirationTimestamp < time();
    }
}
