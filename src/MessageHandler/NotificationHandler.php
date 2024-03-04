<?php

namespace App\MessageHandler;

use App\Message\Notification;
use App\WebSocket\ChatWebSocket;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
final class NotificationHandler
{

    private $chatWebSocket;

    public function __construct(ChatWebSocket $chatWebSocket)
    {
        $this->chatWebSocket = $chatWebSocket;
    }
    public function __invoke(Notification $notification)
    {
        $content = $notification->getContent();
        $userId = $notification->getUserId();
        $email = $notification->getEmail();

        $data = [
            'type' => $$email,
            'content' => $notification->getContent(),
        ];

        $this->chatWebSocket->broadcast($data);
    }
}
