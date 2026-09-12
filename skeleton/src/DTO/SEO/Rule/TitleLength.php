<?php
// src/Seo/Rule/TitleLengthRule.php
namespace App\DTO\SEO\Rule;

use App\DTO\SEO\SeoResult;
use Symfony\Component\DomCrawler\Crawler;
use App\Interface\SEO\Rule\SeoRuleInterface;

final class TitleLength implements SeoRuleInterface
{
    public function check(Crawler $crawler): SeoResult
    {
        $titles = $crawler->filter('title');

        $title = 'Title length';

        if ($titles->count() === 0) {
            return new SeoResult($title, '<title> tag missing', false, 3);
        }

        $length = mb_strlen(trim($titles->text()));

        if ($length === 0) {
            return new SeoResult($title, '<title> tag empty', false, 3);
        }

        if ($length < 30 || $length > 60) {
            return new SeoResult(
                $title,
                sprintf('Title length %d with "%s" characters (ideal : 30-60)', $length, $titles->text()),
                false,
                2
            );
        }

        return new SeoResult('title_length', sprintf('Correct title (%d characters)', true, $length), 2);
    }
}