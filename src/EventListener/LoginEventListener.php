<?php



// src/EventListener/LoginListener.php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginEventListener implements EventSubscriberInterface
{
    private $kernel;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }


    public function onLogin(InteractiveLoginEvent $event)
    {
        /** @var User user */
        $user = $event->getAuthenticationToken()->getUser();
        $user->setIsActiveNow(true);
        $user->setLastActivityAt(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            InteractiveLoginEvent::class => 'onLogin',
        ];
    }
}
