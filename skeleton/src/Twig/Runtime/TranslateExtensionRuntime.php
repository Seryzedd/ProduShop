<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use App\Interface\TranslatableInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TranslateExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private RequestStack $requestStack) {}

    public function getTranslated(TranslatableInterface $entity, string $field): mixed
    {
        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'fr';
        
        if(!$entity->translate($locale)) {
            return $entity->{'get' . ucfirst($field)}();
        }

        return $entity->translate($locale)->{'get' . ucfirst($field)}();
    }
}
