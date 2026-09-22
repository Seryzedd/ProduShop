<?php

namespace App\Service\Department;

use App\Service\Translation\TranslationFileReader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FileGenerator
{
    public function __construct(
        private HttpClientInterface $client,
        private TranslationFileReader $translationFileReader,
        #[Autowire('%app.department_csv_dir%')]
        private string $dataDir
    ) {
    }

    public function generate(bool $force = false): array
    {
        $departements = $this->fetchFromApi();
        $generated = [];

        $departmentPath = $this->generateDepartmentFile($departements, $force);
        if ($departmentPath !== null) {
            $generated['departements'] = $departmentPath;
        }

        return $generated + $this->generateRegionTranslations($departements, $force);
    }

    private function generateDepartmentFile(array $departements, bool $force): ?string
    {
        $path = $this->getDepartmentPath();

        if (!$force && file_exists($path)) {
            return null;
        }

        $data = [];
        foreach ($departements as $dept) {
            $data[$dept['code']] = [
                'nom' => $dept['nom'],
                'code_region' => $dept['region']['code'],
                'nom_region_fr' => $dept['region']['nom'],
            ];
        }

        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, recursive: true);
        }

        file_put_contents($path, Yaml::dump($data, indent: 4, inline: 4));

        return $path;
    }

    private function generateRegionTranslations(array $departements, bool $force): array
    {
        $locales = TranslationFileReader::getLanguagesByFiles();
        $generated = [];

        // Régions dédupliquées depuis l'API (code => nom FR)
        $regionsFromApi = [];
        foreach ($departements as $dept) {
            $regionsFromApi[$dept['region']['nom']] = $dept['region']['nom'];
            $regionsFromApi[$dept['nom']] = $dept['nom'];
        }

        foreach ($locales as $locale) {
            $filename = sprintf('regions.%s.yml', $locale);

            $existing = $this->translationFileReader->readTranslationFileSafe($filename);
            if (!$force && !empty($existing)) {
                continue; // Already translated -> go to next
            }

            // Seed avec les noms FR — les autres locales seront ensuite traduites manuellement
            $this->translationFileReader->updateFile($filename, $regionsFromApi);
            $generated[$locale] = $this->translationFileReader->getFullPathFile($filename);
        }

        return $generated;
    }

    private function fetchFromApi(): array
    {
        $response = $this->client->request('GET', 'https://geo.api.gouv.fr/departements', [
            'query' => ['fields' => 'nom,region'],
        ]);

        return $response->toArray();
    }

    private function getDepartmentPath(): string
    {
        return sprintf('%s/departements.yml', $this->dataDir);
    }
}