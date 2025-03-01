<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProfanityFilterService
{
    private $httpClient;
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    /**
     * Filter profanity from text using Purgomalum API
     * 
     * @param string $text The text to filter
     * @param string $replacement Character to replace profanity with (default: '*')
     * @return string The filtered text
     */
    public function filterText(string $text, string $replacement = '*'): string
    {
        try {
            $response = $this->httpClient->request('GET', 'https://www.purgomalum.com/service/json', [
                'query' => [
                    'text' => $text,
                    'fill_char' => $replacement
                ]
            ]);
            
            $data = $response->toArray();
            
            if (isset($data['result'])) {
                return $data['result'];
            }
            
            return $text; // Return original text if API response doesn't contain result
        } catch (\Exception $e) {
            // Log error here if needed
            return $text; // Return original text in case of API failure
        }
    }
}