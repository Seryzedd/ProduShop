<?php
// src/Seo/KeywordAnalyzer.php
namespace App\Service\Seo;

use Symfony\Component\DomCrawler\Crawler;
use App\DTO\SEO\Keyword\KeyWordReport;
use App\DTO\SEO\Keyword\KeywordCheck;

final class KeywordAnalyzer
{
    // Densité idéale généralement admise entre 0.5% et 2.5%
    private const MIN_DENSITY = 0.5;
    private const MAX_DENSITY = 2.5;

    public function analyze(Crawler $crawler, string $keyword): KeyWordReport
    {
        $keyword = trim(mb_strtolower($keyword));
        $checks = [];

        $checks[] = $this->checkTitle($crawler, $keyword);
        $checks[] = $this->checkH1($crawler, $keyword);
        $checks[] = $this->checkMetaDescription($crawler, $keyword);
        $checks[] = $this->checkFirstParagraph($crawler, $keyword);
        $checks[] = $this->checkUrl($crawler, $keyword);

        $density = $this->computeDensity($crawler, $keyword);
        $checks[] = new KeywordCheck(
            'density',
            $density >= self::MIN_DENSITY && $density <= self::MAX_DENSITY,
            sprintf('Densité : %.2f%% (idéal : %.1f%%-%.1f%%)', $density, self::MIN_DENSITY, self::MAX_DENSITY)
        );

        return new KeyWordReport($keyword, $checks, $density);
    }

    private function checkTitle(Crawler $crawler, string $keyword): KeywordCheck
    {
        $title = mb_strtolower($crawler->filter('title')->text(''));
        $found = str_contains($title, $keyword);

        return new KeywordCheck('title', $found, $found ? 'Présent dans le <title>' : 'Absent du <title>');
    }

    private function checkH1(Crawler $crawler, string $keyword): KeywordCheck
    {
        $h1Nodes = $crawler->filter('h1');
        $found = false;

        $h1Nodes->each(function (Crawler $node) use ($keyword, &$found) {
            if (str_contains(mb_strtolower($node->text('')), $keyword)) {
                $found = true;
            }
        });

        return new KeywordCheck('h1', $found, $found ? 'Présent dans un <h1>' : 'Absent des <h1>');
    }

    private function checkMetaDescription(Crawler $crawler, string $keyword): KeywordCheck
    {
        $meta = $crawler->filter('meta[name="description"]');
        $content = $meta->count() > 0 ? mb_strtolower((string) $meta->attr('content')) : '';
        $found = str_contains($content, $keyword);

        return new KeywordCheck('meta_description', $found, $found ? 'Présent dans la meta description' : 'Absent de la meta description');
    }

    private function checkFirstParagraph(Crawler $crawler, string $keyword): KeywordCheck
    {
        $paragraphs = $crawler->filter('p');

        if ($paragraphs->count() === 0) {
            return new KeywordCheck('first_paragraph', false, 'Aucun <p> trouvé');
        }

        $firstText = mb_strtolower($paragraphs->first()->text(''));
        $found = str_contains($firstText, $keyword);

        return new KeywordCheck('first_paragraph', $found, $found ? 'Présent dans le premier paragraphe' : 'Absent du premier paragraphe');
    }

    private function checkUrl(Crawler $crawler, string $keyword): KeywordCheck
    {
        $url = mb_strtolower($crawler->getUri() ?? '');
        // On compare une version "slug" du mot-clé (espaces -> tirets)
        $slugKeyword = str_replace(' ', '-', $keyword);
        $found = $url !== '' && str_contains($url, $slugKeyword);

        return new KeywordCheck('url', $found, $found ? 'Présent dans l\'URL' : 'Absent de l\'URL');
    }

    private function computeDensity(Crawler $crawler, string $keyword): float
    {
        // On retire scripts/styles pour ne pas fausser le comptage
        $crawler->filter('script, style')->each(function (Crawler $node) {
            foreach ($node as $domNode) {
                $domNode->parentNode?->removeChild($domNode);
            }
        });

        $bodyNodes = $crawler->filter('body');
        if ($bodyNodes->count() === 0) {
            return 0.0;
        }

        $text = mb_strtolower($bodyNodes->text('', true));
        $totalWords = str_word_count($text);

        if ($totalWords === 0) {
            return 0.0;
        }

        // Comptage du mot-clé, y compris s'il est composé de plusieurs mots
        $occurrences = substr_count($text, $keyword);
        $keywordWordCount = max(1, str_word_count($keyword));

        return round(($occurrences * $keywordWordCount / $totalWords) * 100, 2);
    }
}