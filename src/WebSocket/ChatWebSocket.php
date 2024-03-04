<?php

// src/WebSocket/ChatWebSocket.php
namespace App\WebSocket;

use App\Entity\User;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatWebSocket implements MessageComponentInterface
{
    protected $clients;
    protected $messages;
    protected $emailToResourceIdMap;
    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->messages = [];
        $this->emailToResourceIdMap = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {

        $this->clients->attach($conn);

        // Assigner un resourceId (identifiant) au client
        $resourceId = $conn->resourceId;
        // Supposons que vous ayez une méthode pour obtenir l'e-mail de l'utilisateur
        // /** @var User user  */
        // $user = $this->getUser();
        $userEmail = 'user@gmail.com';
        // Ajouter la correspondance e-mail => resourceId
        $this->emailToResourceIdMap[$userEmail] = $resourceId;

        echo "Nouvelle connexion ({$resourceId})\n";
    }


    public function sendMessageToUser($recipientEmail, $content, $title = null)
    {
        if (isset($this->emailToResourceIdMap[$recipientEmail])) {
            $recipientResourceId = $this->emailToResourceIdMap[$recipientEmail];

            foreach ($this->clients as $client) {
                if ($client->resourceId == $recipientResourceId) {
                    $data = [
                        'type' => 'message',
                        'content' => $content,
                        'title' => $title,  // Utilisez le titre du message s'il est défini
                        'recipient' => $recipientEmail,  // Utilisez le destinataire du message s'il est défini
                    ];

                    $client->send(json_encode($data));

                    $this->addMessage($client->resourceId, $content, $title, $recipientEmail);

                    return true; // Message envoyé avec succès
                }
            }
        }

        return false; // Destinataire non trouvé
    }
    public function onMessage(ConnectionInterface $from, $msg)
    {

        $data = json_decode($msg, true);

        if ($data['type'] === 'message') {

            $this->addMessage(
                $from->resourceId,
                $data['content'],
                'received',
                $data['title'] ?? null,
                $data['recipient'] ?? null
            );

            $this->broadcast($data);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connexion fermée ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Erreur : {$e->getMessage()}\n";
        $conn->close();
    }


    public function broadcast($data)
    {
        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }
        $this->addMessage('server', $data['content'], 'sent');
    }
    public function getMessages()
    {
        return $this->messages;
    }
    protected function addMessage($from, $content, $type, $title = null, $recipient = null)
    {
        $this->messages[] = [
            'from' => $from,
            'content' => $content,
            'timestamp' => time(),
            'type' => $type,
            'title' => $title,
            'recipient' => $recipient,
        ];
    }

    public function getResourceIds()
    {
        $resourceIds = [];

        foreach ($this->clients as $client) {
            $resourceIds[] = $client->resourceId;
        }

        return $resourceIds;
    }
}
