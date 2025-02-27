<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImageGenerationController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/generate-image', name: 'generate_image', methods: ['POST'])]
    public function generateImage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $description = $data['description'] ?? '';

        // Traduction simple de quelques mots clés français vers l'anglais
        $translations = [
            'formation' => 'training',
            'atelier' => 'workshop',
            'cours' => 'class',
            'salle' => 'room',
            'artisan' => 'craftsman',
            'élèves' => 'students',
            'professeur' => 'teacher',
            'lumière' => 'light',
            'moderne' => 'modern',
            'traditionnel' => 'traditional'
        ];

        // Remplacer les mots français par leur équivalent en anglais
        $englishDescription = str_replace(
            array_keys($translations),
            array_values($translations),
            $description
        );

        try {
            $response = $this->httpClient->request('POST', 'https://api.edenai.run/v2/image/generation', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['EDENAI_API_KEY'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'providers' => 'stabilityai',
                    'text' => $englishDescription,
                    'resolution' => '512x512',
                    'num_images' => 1
                ]
            ]);

            $result = json_decode($response->getContent(), true);
            
            // Ajout du logging pour debug
            if (!isset($result['stabilityai']['items'][0]['image_resource_url'])) {
                return new JsonResponse([
                    'error' => 'Erreur API: ' . json_encode($result)
                ], 500);
            }

            $imageUrl = $result['stabilityai']['items'][0]['image_resource_url'];
            return new JsonResponse(['imageUrl' => $imageUrl]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }
} 