<?php
// src/Seo/KeywordCheck.php
namespace App\DTO\SEO\Keyword;

final class KeywordCheck
{
    public function __construct(
        public readonly string $label,
        public readonly bool $found,
        public readonly string $detail = '',
    ) {
    }
}