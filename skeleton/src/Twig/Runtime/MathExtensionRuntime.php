<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class MathExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function calculatePi()
    {
        return M_PI;
    }

    public function calculateCos(float $value): float
    {
        return cos($value);
    }

    public function calculateSin(float $value): float
    {
        return sin($value);
    }
}
