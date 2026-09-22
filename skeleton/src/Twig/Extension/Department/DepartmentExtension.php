<?php

namespace App\Twig\Extension\Department;

use App\Twig\Runtime\Department\DepartmentExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DepartmentExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('department', [DepartmentExtensionRuntime::class, 'ShowDepartmentDatas']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('department', [DepartmentExtensionRuntime::class, 'ShowDepartmentDatas']),
        ];
    }
}
