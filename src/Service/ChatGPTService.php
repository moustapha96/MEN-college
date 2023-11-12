<?php

namespace App\Service;

use OpenAI;

use OpenAI\Client;

class ChatGPTService
{
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function generateResponse(string $prompt): string
    {
        $client = OpenAI::client($this->apiKey);

        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo-instruct',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        return  $response->choices[0]->message->content;
    }
}
