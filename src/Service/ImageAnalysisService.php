<?php
namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class ImageAnalysisService
{
    private Client $client;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(string $apiKey, LoggerInterface $logger)
    {
        $this->client = new Client();
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    public function analyzeImage(string $imagePath): array
    {
        $url = 'https://api-inference.huggingface.co/models/facebook/deit-base-distilled-patch16-224';

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/octet-stream',
                ],
                'body' => file_get_contents($imagePath),
            ]);
            

            $data = json_decode($response->getBody(), true);

            if (isset($data['error'])) {
                $this->logger->error("Erreur API Hugging Face : " . $data['error']);
                return ['error' => $data['error']];
            }

            if (!is_array($data) || empty($data)) {
                $this->logger->warning("Réponse vide ou inattendue de l'API.");
                return ['error' => 'Réponse vide ou incorrecte'];
            }

            // Exemple d'extraction de score basé sur la probabilité du premier label
            $topPrediction = $data[0] ?? null;
            if ($topPrediction && isset($topPrediction['score'])) {
                $score = (int) round($topPrediction['score'] * 100);
                $this->logger->info("Analyse d'image effectuée avec un score de : " . $score);
                return ['score' => $score];
            }

            $this->logger->warning("Aucun score trouvé dans la réponse de l'API.");
            return ['error' => 'Pas de score trouvé'];

        } catch (RequestException $e) {
            $this->logger->error("Erreur de requête vers Hugging Face : " . $e->getMessage());
            return ['error' => 'Erreur API'];
        }
    }
}
