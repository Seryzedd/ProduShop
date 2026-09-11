<?php

namespace App\Twig\Runtime\Seo;

use Twig\Extension\RuntimeExtensionInterface;
use App\Service\Seo\Analyzer;
use Twig\Environment;

class ViewAnalyzeExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private Environment $twig)
    {
        // Inject dependencies if needed
    }

    public function viewAnalyze(Analyzer $analyzer)
    {
        return $this->twig->render('components/seo/analyze_report.html.twig', [
            'report' => $analyzer->getHtmlReport(),
            'html' => $analyzer->html,
            'keywords' => $analyzer->getkeywordsReport()
        ]);
    }
}
