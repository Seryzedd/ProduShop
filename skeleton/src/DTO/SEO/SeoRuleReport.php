<?php
// src/Seo/SeoAuditReport.php
namespace App\DTO\SEO;

final class SeoRuleReport
{
    /** @param SeoRuleResult[] $results */
    public function __construct(private readonly array $results)
    {
        $this->score = $this->score();
        $this->failures = $this->failures();
    }

    public function score(): int
    {
        $totalWeight = array_sum(array_map(fn ($r) => $r->getWeight(), $this->results));
        $earnedWeight = array_sum(array_map(fn ($r) => $r->isPassed() ? $r->getWeight() : 0, $this->results));

        return $totalWeight > 0 ? (int) round(($earnedWeight / $totalWeight) * 100) : 0;
    }

    public function failures(): array
    {
        return array_filter($this->results, fn ($r) => !$r->isPassed());
    }

    public function getScore()
    {
        return $this->score;
    }

    public function getFailures()
    {
        return $this->failures;
    }

    public function getResults()
    {
        return $this->results;
    }
}