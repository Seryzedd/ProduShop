<?php
// src/Seo/KeywordAuditReport.php
namespace App\DTO\SEO\Keyword;

final class KeyWordReport
{
    /** @param KeywordCheck[] $checks */
    public function __construct(
        public readonly string $keyword,
        public readonly array $checks,
        public readonly float $density,
    ) {
    }

    public function score(): int
    {
        $total = count($this->checks);
        $found = count(array_filter($this->checks, fn ($c) => $c->found));

        return $total > 0 ? (int) round(($found / $total) * 100) : 0;
    }
}