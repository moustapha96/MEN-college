<?php

namespace App\Service;

use OpenAI;


class ChatGPTService
{
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function generateResponse(string $prompt): string
    {

        try {
            $client = OpenAI::client("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");

            $response = $client->chat()->create([
                'model' => 'text-davinci-003',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            return  $response->choices[0]->message->content;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
