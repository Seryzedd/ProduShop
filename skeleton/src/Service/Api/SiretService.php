<?php

namespace App\Service\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Developed by the interministerial mission Etalab,
 * this API is ideal if you're looking for a solution without any registration or API key.
 * It aggregates data from INSEE (Sirene), the RNCS, and other official databases.
 * data.gouv.fr +2 Documentation link: Business Search API Documentation How to use it (Example GET query): Simply pass the SIRET number (14 digits) in the search parameter q: https://api.gouv.fr Limitations: Completely free…
 */
class SiretService extends AbstractApi
{
    private function search(string $query, int $perPage = 1): array
    {
        $response = $this->client->request('GET', 'https://recherche-entreprises.api.gouv.fr/search', [
            'query' => [
                'q' => $query,
                'per_page' => $perPage,
            ],
        ]);

        return $response->toArray()['results'] ?? [];
    }

    public function findBySiret(string $siret): ?array
    {
        $results = $this->search($siret);
        return $results[0] ?? null;
    }

    public function findByName(string $name): ?array
    {
        $results = $this->search($name);
        return $results[0] ?? null;
    }
}