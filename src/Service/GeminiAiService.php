<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use Exception;

class GeminiAiService
{
    private $httpClient;
    private $apiKey;
    private $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $parameterBag,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $parameterBag->get('gemini_api_key');
        $this->logger = $logger;
    }

    public function generateProjectDetails(string $prompt): array
    {
        // Define API endpoints to try in order - prioritize the working endpoint
        $endpoints = [
            // This endpoint is confirmed to be working
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent",
            // Fallback endpoints
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent",
            "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent",
            "https://us-central1-aiplatform.googleapis.com/v1/projects/PROJECT_ID/locations/us-central1/publishers/google/models/gemini-pro:generateContent"
        ];
        
        $lastError = null;
        
        // Try each endpoint until one works
        foreach ($endpoints as $baseUrl) {
            $url = "{$baseUrl}?key={$this->apiKey}";
            
            // Special handling for Vertex AI endpoint
            if (strpos($baseUrl, 'aiplatform.googleapis.com') !== false) {
                // Replace PROJECT_ID with a placeholder - the API key should work regardless
                $url = str_replace('PROJECT_ID', 'gemini-api-project', $url);
            }
            
            $result = $this->callGeminiApi($url, $prompt);
            
            // If successful, return the result
            if (!isset($result['error'])) {
                return $result;
            }
            
            // Store the error for potential fallback
            $lastError = $result['error'];
            $this->logger->warning("API endpoint failed: {$baseUrl}. Trying next endpoint.", ['error' => $lastError]);
        }
        
        // If all endpoints failed, return the last error
        return ['error' => "All API endpoints failed. Last error: {$lastError}"];
    }
    
    private function callGeminiApi(string $url, string $prompt): array
    {
        $this->logger->info('Calling Gemini API with prompt: ' . substr($prompt, 0, 50) . '...');
        
        // Determine if we're using Vertex AI or regular Gemini API
        $isVertexAi = strpos($url, 'aiplatform.googleapis.com') !== false;
        
        // Updated payload format for Google AI Studio
        $payload = $isVertexAi ? $this->getVertexAiPayload($prompt) : $this->getGeminiPayload($prompt);

        try {
            $this->logger->debug('Sending request to Gemini API', ['url' => $url]);
            
            $response = $this->httpClient->request('POST', $url, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $this->logger->debug('Received response from Gemini API', ['status_code' => $statusCode]);
            
            if ($statusCode !== 200) {
                $errorContent = $response->getContent(false);
                $this->logger->error('Gemini API returned non-200 status code', [
                    'status_code' => $statusCode,
                    'response' => $errorContent
                ]);
                return ['error' => "API returned status code {$statusCode}: {$errorContent}"];
            }

            $data = $response->toArray();
            
            // Extract content based on API type
            $content = $isVertexAi 
                ? $this->extractVertexAiContent($data)
                : $this->extractGeminiContent($data);
                
            if ($content === null) {
                return ['error' => 'Unexpected response structure from API'];
            }
            
            $this->logger->debug('Received content from API', ['content_length' => strlen($content)]);
            
            // Extract JSON from the response text
            preg_match('/\{.*\}/s', $content, $matches);
            if (empty($matches)) {
                $this->logger->error('Failed to extract JSON from AI response', [
                    'content' => $content
                ]);
                return ['error' => 'Failed to extract JSON from AI response. Raw content: ' . substr($content, 0, 200)];
            }
            
            $jsonData = json_decode($matches[0], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonError = json_last_error_msg();
                $this->logger->error('Invalid JSON in AI response', [
                    'json_error' => $jsonError,
                    'content' => $matches[0]
                ]);
                return ['error' => "Invalid JSON in AI response: {$jsonError}. Raw content: " . substr($matches[0], 0, 200)];
            }
            
            $result = [
                'titre' => $jsonData['titre'] ?? '',
                'description' => $jsonData['description'] ?? '',
                'localisation' => $jsonData['localisation'] ?? '',
                'goalamount' => (float)($jsonData['goalamount'] ?? 100)
            ];
            
            $this->logger->info('Successfully generated project details', [
                'titre' => substr($result['titre'], 0, 30) . '...',
                'description_length' => strlen($result['description']),
                'localisation' => $result['localisation'],
                'goalamount' => $result['goalamount']
            ]);
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Exception in Gemini API call', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'error' => 'Failed to generate project details: ' . $e->getMessage()
            ];
        }
    }
    
    private function getGeminiPayload(string $prompt): array
    {
        return [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "Génère un projet de financement participatif basé sur cette description : \"{$prompt}\". 
                            Retourne un objet JSON avec ces champs (la réponse doit être UNIQUEMENT en français) :
                            - titre: Un titre accrocheur en français (maximum 255 caractères)
                            - description: Une description détaillée en français (minimum 100 caractères)
                            - localisation: Où le projet aura lieu (en français)
                            - goalamount: Un objectif de financement (nombre, minimum 100)"
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1000
            ]
        ];
    }
    
    private function getVertexAiPayload(string $prompt): array
    {
        return [
            'instances' => [
                [
                    'prompt' => "Génère un projet de financement participatif basé sur cette description : \"{$prompt}\". 
                    Retourne un objet JSON avec ces champs (la réponse doit être UNIQUEMENT en français) :
                    - titre: Un titre accrocheur en français (maximum 255 caractères)
                    - description: Une description détaillée en français (minimum 100 caractères)
                    - localisation: Où le projet aura lieu (en français)
                    - goalamount: Un objectif de financement (nombre, minimum 100)"
                ]
            ],
            'parameters' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1000,
                'topK' => 40,
                'topP' => 0.95
            ]
        ];
    }
    
    private function extractGeminiContent(array $data): ?string
    {
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }
    
    private function extractVertexAiContent(array $data): ?string
    {
        return $data['predictions'][0]['content'] ?? null;
    }

    public function generateEventContent($prompt)
    {
        try {
            $client = new Client([
                'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = $client->post('models/gemini-pro:generateContent?key=' . $this->apiKey, [
                'json' => [
                    'contents' => [
                        'parts' => [
                            [
                                'text' => "Génère une réponse en français uniquement pour : " . $prompt
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 800,
                    ]
                ]
            ]);

            // ... rest of the existing code ...
        }
        catch (Exception $e) {
            // ... existing error handling ...
        }
    }
} 