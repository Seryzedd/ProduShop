<?php
// src/Seo/SeoAnalyzer.php
namespace App\Service\Seo;

use App\Interface\SEO\Rule\SeoRuleInterface;
use Symfony\Component\DomCrawler\Crawler;
use App\DTO\SEO\SeoRuleReport;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

final class Analyzer
{
    public string $html = '';

    /** @param iterable<SeoRuleInterface> $rules */
    public function __construct(private readonly iterable $rules, private KeywordAnalyzer $keywordAnalyzer)
    {
        $this->keywordsReport = new ArrayCollection();
        $this->htmlReport = null;
    }

    public function analyze(string $html): self
    {
        $this->html = $html;

        $crawler = new Crawler($html);
        $results = [];

        foreach ($this->rules as $rule) {
            $results[] = $rule->check($crawler);
        }

        $result = new SeoRuleReport($results);

        $this->htmlReport = $result;

        return $this;
    }

    public function analyzeKeywords(Collection $keywords, string $html)
    {
        $crawler = new Crawler($html);
        foreach($keywords as $keyword) {
            $label = $keyword->getLabel();

            $this->keywordsReport->add($this->keywordAnalyzer->analyze($crawler, $label));
        }
        
        return $this;
    }

    public function getKeywordsReport()
    {
        return $this->keywordsReport;
    }

    public function getHtmlReport(): SeoRuleReport
    {
        return $this->htmlReport;
    }
}