<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DandelionService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function analyzeText(string $text): array
    {
        $response = $this->client->request('GET', 'https://api.dandelion.eu/datatxt/nex/v1', [
            'query' => [
                'text' => $text,
                'lang' => 'fr', // Ajout de la langue pour de meilleurs résultats
                'include' => 'categories', // Ajout de l'inclusion des catégories
                'token' => $this->apiKey,
            ]
        ]);

        return $response->toArray();
    }
}