<?php



// src/Service/OpenAIService.php

namespace App\Service;

use GuzzleHttp\Client;

use OpenAI;

class OpenAIService
{
    private $apiKey;
    private $openaiEndpoint;

    public function __construct(string $apiKey, string $openaiEndpoint)
    {
        $this->apiKey = $apiKey;
        $this->openaiEndpoint = $openaiEndpoint;
    }

    public function generateReport(string $collegeName): string
    {
        $client = new Client();

        $client = OpenAI::client("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");


        // $response = $client->chat()->create([
        //     'prompt' => $collegeName,
        //     'model' => 'text-davinci-003', // Modèle spécifique à choisir en fonction de vos besoins.
        // ]);


        $response = $client->chat()->create(
            [
                'model' => 'gpt-3.5-turbo',
                'messages' => [[
                    'role' => 'user',
                    'content' => 'Hello!',
                ]],
            ]
        );

        return $response['choices'][0]['message']['content'];


        // try {
        //     $response = $client->post($this->openaiEndpoint . '/v1/engines/davinci-codex/completions', [
        //         'headers' => [
        //             'Authorization' => 'Bearer ' . $this->apiKey,
        //         ],
        //         'json' => [
        //             'prompt' => 'Générer un rapport pour ' . $collegeName,
        //             'temperature' => 0.7,
        //             'max_tokens' => 200,
        //         ],
        //     ]);

        //     $data = json_decode($response->getBody(), true);

        //     return $data['choices'][0]['text'] ?? '';
        // } catch (\Throwable $th) {
        //     throw $th;
        // }
    }
}
