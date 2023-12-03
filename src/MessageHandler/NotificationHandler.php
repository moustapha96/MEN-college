<?php

namespace App\MessageHandler;

use App\Message\Notification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Notification\Notification as NotifierNotification;



#[AsMessageHandler]
final class NotificationHandler implements MessageHandlerInterface
{
    private $notifier;
    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }
    public function __invoke(Notification $notification)
    {
        $content = $notification->getContent();
        $userId = $notification->getUserId();
        $email = $notification->getContent();
        // $notification = new EmailMessage(  $email, 'Sujet de la notification');

        // $recipient = new Recipient('', 'user@example.com');
        // $notification = new NotifierNotification($content);
        // $this->notifier->send($notification, $recipient);
    }
}
