<?php
// src/Seo/Rule/TitleLengthRule.php
namespace App\DTO\SEO\Rule;

use App\DTO\SEO\SeoResult;
use Symfony\Component\DomCrawler\Crawler;
use App\Interface\SEO\Rule\SeoRuleInterface;

final class MetaDescription implements SeoRuleInterface
{
    public function check(Crawler $crawler): SeoResult
    {
        $meta = $crawler->filter('meta[name="description"]');
        $name = 'Meta description';
        if ($meta->count() === 0) {
            return new SeoResult($name, 'No meta description', false, 3);
        }

        $content = trim((string) $meta->attr('content'));
        $length = mb_strlen($content);

        if ($length === 0) {
            return new SeoResult($name, 'Meta description empty', false, 3);
        }

        if ($length < 120 || $length > 160) {
            return new SeoResult(
                $name,
                sprintf('Description of %d characters (ideal : 120-160)', $length),
                false,
                2
            );
        }

        return new SeoResult($name, 'Correct Meta description', false, 2);
    }
}