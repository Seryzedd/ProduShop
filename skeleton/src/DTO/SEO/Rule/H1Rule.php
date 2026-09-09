<?php
// src/Seo/Rule/TitleLengthRule.php
namespace App\DTO\SEO\Rule;

use App\DTO\SEO\SeoResult;
use Symfony\Component\DomCrawler\Crawler;
use App\Interface\SEO\Rule\SeoRuleInterface;

final class H1Rule implements SeoRuleInterface
{
    public function check(Crawler $crawler): SeoResult
    {
        $filter = $crawler->filter('h1');
        $count = $filter->count();

        $name = 'H1 tag';
        if ($count === 0) {
            return new SeoResult($name, 'No <h1> found', false, 3);
        }

        if ($count > 1) {
            return new SeoResult($name,sprintf('%d length with %d <h1> tags found (Only one waited)', $count, $filter->text()), false, 2);
        }

        return new SeoResult($name, 'One <h1> found', true, 2);
    }
}