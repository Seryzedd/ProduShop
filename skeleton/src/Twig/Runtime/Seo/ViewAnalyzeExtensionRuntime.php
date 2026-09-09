<?php

namespace App\Twig\Runtime\Seo;

use Twig\Extension\RuntimeExtensionInterface;
use App\DTO\SEO\SeoRuleReport;
use Twig\Environment;

class ViewAnalyzeExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private Environment $twig)
    {
        // Inject dependencies if needed
    }

    public function viewAnalyze(SeoRuleReport $report, string $html)
    {
        return $this->twig->render('components/seo/analyze_report.html.twig', [
            'report' => $report,
            'html' => $html
        ]);
    }
}
