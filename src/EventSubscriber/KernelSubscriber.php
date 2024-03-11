<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use App\EventListener\DatabaseInitializer;


class KernelSubscriber implements EventSubscriberInterface
{
    private $databaseInitializer;

    public function __construct(DatabaseInitializer $databaseInitializer)
    {
        $this->databaseInitializer = $databaseInitializer;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->isMainRequest()) {
            $this->databaseInitializer->initialize();
        }
    }
}
