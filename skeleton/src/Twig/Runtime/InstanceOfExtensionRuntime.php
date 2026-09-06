<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class InstanceOfExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function instanceOf(mixed $value, string $class)
    {
        if($class === 'array') {
            return is_array($value);
        }

        if($class === 'string') {
            return is_string($value);
        }

        return $value instanceof $class;
    }
}
