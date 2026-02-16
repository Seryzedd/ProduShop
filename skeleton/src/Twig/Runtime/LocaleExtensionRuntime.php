<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use \Locale;

class LocaleExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private RequestStack $requestStack)
    {
        // Inject dependencies if needed
    }

    public function getRegion(string $value)
    {
        return Locale::getDisplayRegion($value, $this->getRequest()->getLocale());
    }

    public function getCountry(string $value)
    {
        return Locale::getDisplayRegion($value, $this->getRequest()->getLocale());
    }

    public function getLanguage(string $value)
    {
        return Locale::getDisplayLanguage($value, $this->getRequest()->getLocale());
    }

    public function getName(string $value)
    {
        return Locale::getDisplayName($value, $this->getRequest()->getLocale());
    }

    private function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}
