<?php

// src/EventListener/LogoutListener.php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class LogoutEventListener implements EventSubscriberInterface
{
    private $kernel;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    public function onLogout(LogoutEvent $event)
    {
        $token = $event->getToken();
        if ($token) {
            $user = $token->getUser();

            $user->setIsActiveNow(false);
            $user->setLastActivityAt(new \DateTime());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
