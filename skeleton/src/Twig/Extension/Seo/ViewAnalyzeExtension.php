<?php

namespace App\Twig\Extension\Seo;

use App\Twig\Runtime\Seo\ViewAnalyzeExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ViewAnalyzeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('viewAnalyze', [ViewAnalyzeExtensionRuntime::class, 'viewAnalyze']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('viewAnalyze', [ViewAnalyzeExtensionRuntime::class, 'viewAnalyze']),
        ];
    }
}
