<?php
// src/Seo/Rule/SeoRuleInterface.php
namespace App\Interface\SEO\Rule;

use App\DTO\SEO\SeoResult;
use Symfony\Component\DomCrawler\Crawler;

interface SeoRuleInterface
{
    public function check(Crawler $crawler): SeoResult;
}