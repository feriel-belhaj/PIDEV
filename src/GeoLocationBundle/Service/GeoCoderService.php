<?php

namespace App\GeoLocationBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeoCoderService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function geocodeAddress(string $address): ?array
    {
        $encodedAddress = urlencode($address . ', Tunisia');
        $response = $this->httpClient->request(
            'GET',
            "https://nominatim.openstreetmap.org/search?format=json&q={$encodedAddress}&limit=1"
        );

        if ($response->getStatusCode() === 200) {
            $data = $response->toArray();
            if (!empty($data)) {
                return [
                    'lat' => $data[0]['lat'],
                    'lon' => $data[0]['lon'],
                ];
            }
        }

        return null; // Retourne null si aucune donnée n'est trouvée
    }
} 