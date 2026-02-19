<?php
// src/Service/GeocoderService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeocoderService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Retourne [latitude, longitude] pour une adresse donnée,
     * ou null si le géocodage échoue.
     *
     * @return array{float, float}|null
     */
    public function geocode(string $address): ?array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q'      => $address,
                    'format' => 'json',
                    'limit'  => 1,
                ],
                'headers' => [
                    // Nominatim exige un User-Agent identifiant votre application
                    'User-Agent' => 'MonAppSymfony/1.0 (contact@monapp.fr)',
                ],
                'timeout' => 5,
            ]);

            $data = $response->toArray();

            if (empty($data)) {
                $this->logger->warning('Geocoder: aucun résultat pour "{address}"', ['address' => $address]);
                return null;
            }

            return [(float) $data[0]['lat'], (float) $data[0]['lon']];

        } catch (\Throwable $e) {
            $this->logger->error('Geocoder: erreur pour "{address}": {error}', [
                'address' => $address,
                'error'   => $e->getMessage(),
            ]);
            return null;
        }
    }
}
