<?php

namespace App\Service\Department;

use App\DTO\Department\DepartmentDTO;
use App\DTO\Department\DepartmentsCollection;
use Symfony\Component\HttpFoundation\RequestStack;

class DepartmentService
{
    public function __construct(
        private DepartmentYmlReader $reader,
        private FileGenerator $fileGenerator,
        private RequestStack $requestStack,
    ) {
    }

    public function getAll(?string $locale = null): DepartmentsCollection
    {
        $locale ??= $this->resolveLocale();

        $dto = new DepartmentsCollection();

        foreach ($this->reader->getAll($locale) as $row) {
            $dto->addDepartment($this->mapRowToDTO($row));
        }

        return $dto;
    }

    public function find(string $code, ?string $locale = null): ?DepartmentDTO
    {
        return $this->getAll($locale)->findByCode($code);
    }

    public function generate(bool $force = false): array
    {
        return $this->fileGenerator->generate($force);
    }

    private function resolveLocale(): string
    {
        return $this->requestStack->getCurrentRequest()?->getLocale() ?? 'fr';
    }

    private function mapRowToDTO(array $row, array $regionTranslations = []): DepartmentDTO
    {
        return new DepartmentDTO(
            $row['code'],
            $row['nom'],
            $row['code_region'],
            $row['nom_region_fr']
        );
    }
}