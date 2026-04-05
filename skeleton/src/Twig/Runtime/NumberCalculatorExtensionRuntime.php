<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class NumberCalculatorExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function calculatePercent(int $value, int $total): float
    {
        
        return ($value / $total) *100;
    }
}
