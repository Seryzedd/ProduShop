<?php
// src/Seo/Rule/TitleLengthRule.php
namespace App\DTO\SEO\Rule;

use App\DTO\SEO\SeoResult;
use Symfony\Component\DomCrawler\Crawler;
use App\Interface\SEO\Rule\SeoRuleInterface;

final class ImageRule implements SeoRuleInterface
{
    public function check(Crawler $crawler): SeoResult
    {
        $images = $crawler->filter('img');
        $total = $images->count();

        $name = 'Pictures tags';
        if ($total === 0) {
            return new SeoResult($name, 'No picture found', true, 1);
        }

        $missing = $images->reduce(fn (Crawler $img) => !$img->attr('alt') || trim($img->attr('alt')) === '')->count();

        if ($missing > 0) {
            return new SeoResult(
                $name,
                sprintf('%d pictures(s)/%d without alt attribute', $missing, $total),
                false,
                2
            );
        }

        return new SeoResult($name, 'Toutes les images ont un attribut alt', true, 2);
    }
}