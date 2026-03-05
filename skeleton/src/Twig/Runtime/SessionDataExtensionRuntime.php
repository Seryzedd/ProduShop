<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionDataExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private RequestStack $requestStack)
    {
        // Inject dependencies if needed
    }

    public function getSessionValue(string $value)
    {
        $session = $this->requestStack->getSession();

        return (bool) $session->get($value);
    }
}
