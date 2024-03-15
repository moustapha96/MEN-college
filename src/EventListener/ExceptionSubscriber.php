<?php

// src/EventSubscriber/ExceptionSubscriber.php

namespace App\EventListener;

use Proxies\__CG__\App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;

class ExceptionSubscriber extends AbstractController implements EventSubscriberInterface
{
    private $params;
    private $tokenStorage;
    private $router;
    private $passwordEncoder;


    public function __construct(
        ParameterBagInterface $params,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,

    ) {
        $this->params = $params;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
    }



    /**
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
        //     $response = $this->render('layouts/404.html.twig');
        //     $event->setResponse($response);
        // }
    }

    public function onKernelRequest(RequestEvent $event)
    {
        /** @var User user */
        $user = $this->getUser();
        if ($user && $user->isEnabled() == false) {
            $this->addFlash('error', "Votre compte a été desactivé");
            $response = new RedirectResponse($this->router->generate('app_logout'));
            $event->setResponse($response);
        }
    }
}
