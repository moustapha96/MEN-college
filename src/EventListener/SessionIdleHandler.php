<?php




namespace App\EventListener;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionIdleHandler
{



    private TokenStorageInterface $securityToken;
    private RouterInterface $router;
    private int $maxIdleTime;

    public function __construct(int $maxIdleTime, TokenStorageInterface $securityToken, RouterInterface $router)
    {
        $this->securityToken = $securityToken;
        $this->router = $router;
        $this->maxIdleTime = $maxIdleTime;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        if ($this->maxIdleTime > 0 && $this->securityToken->getToken() !== null) {
            $session = $event->getRequest()->getSession();

            if (!$session->isStarted()) {
                $session->start();
            }

            $lapse = time() - $session->getMetadataBag()->getLastUsed();

            if ($lapse > $this->maxIdleTime) {
                $this->securityToken->setToken(null);
                $event->setResponse(new RedirectResponse($this->router->generate('app_logout')));
            }
        }
    }
}
