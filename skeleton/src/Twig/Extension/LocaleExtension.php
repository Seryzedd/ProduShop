<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\LocaleExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class LocaleExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('region', [LocaleExtensionRuntime::class, 'getRegion']),
            new TwigFilter('country', [LocaleExtensionRuntime::class, 'getCountry']),
            new TwigFilter('language', [LocaleExtensionRuntime::class, 'getLanguage']),
            new TwigFilter('name', [LocaleExtensionRuntime::class, 'getName']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [LocaleExtensionRuntime::class, 'doSomething']),
        ];
    }
}
