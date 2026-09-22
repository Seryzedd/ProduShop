<?php

namespace App\Service\Department;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

class DepartmentYmlReader
{
    public function __construct(
        #[Autowire('%app.department_csv_dir%')]
        private string $dataDir
    ) {
    }

    public function getAll(string $locale = 'fr'): array
    {
        $path = $this->getPath($locale);

        if (!file_exists($path)) {
            return [];
        }

        $data = Yaml::parseFile($path);

        $rows = [];
        foreach ($data as $code => $row) {
            $rows[] = array_merge(['code' => $code], $row);
        }

        return $rows;
    }

    public function find(string $code, string $locale = 'fr'): ?array
    {
        $path = $this->getPath($locale);

        if (!file_exists($path)) {
            return null;
        }

        $data = Yaml::parseFile($path);

        return isset($data[$code]) ? array_merge(['code' => $code], $data[$code]) : null;
    }

    public function exists(string $locale): bool
    {
        return file_exists($this->getPath($locale));
    }

    public function getPath(string $locale): string
    {
        return sprintf('%s/departements.yml', $this->dataDir, $locale);
    }
}