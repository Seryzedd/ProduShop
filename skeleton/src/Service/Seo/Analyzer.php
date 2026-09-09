<?php
// src/Seo/SeoAnalyzer.php
namespace App\Service\Seo;

use App\Interface\SEO\Rule\SeoRuleInterface;
use Symfony\Component\DomCrawler\Crawler;
use App\DTO\SEO\SeoRuleReport;

final class Analyzer
{
    /** @param iterable<SeoRuleInterface> $rules */
    public function __construct(private readonly iterable $rules)
    {
    }

    public function analyze(string $html): SeoRuleReport
    {
        $crawler = new Crawler($html);
        $results = [];

        foreach ($this->rules as $rule) {
            $results[] = $rule->check($crawler);
        }

        return new SeoRuleReport($results);
    }
}