<?php

namespace App\Twig\Runtime\Department;

use Twig\Extension\RuntimeExtensionInterface;
use App\Service\Department\DepartmentService;

class DepartmentExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private DepartmentService $departmentService)
    {
        // Inject dependencies if needed
    }

    public function ShowDepartmentDatas(string $code, ?int $locale = null)
    {
        return $this->departmentService->find($code, $locale);
    }
}
