<?php



// src/Service/OpenAIService.php

namespace App\Service;

use GuzzleHttp\Client;

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
        try {
            $response = $client->post($this->openaiEndpoint . '/v1/engines/davinci-codex/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
                'json' => [
                    'prompt' => 'Générer un rapport pour ' . $collegeName,
                    'temperature' => 0.7,
                    'max_tokens' => 200,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['choices'][0]['text'] ?? '';
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
