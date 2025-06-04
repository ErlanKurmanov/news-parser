<?php

namespace App\Parser;

use App\DTO\NewsArticleDto;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Azattyk News Parser
 *
 * Concrete parser implementation for Azattyk news source.
 * Handles the specific DOM structure and content patterns used by the Azattyk website,
 * including multi-language content cleanup for Russian text.
 */
class AzattykParser extends AbstractParser
{
    /**
     * Parses Azattyk HTML content and extracts news articles
     *
     * Implements parsing logic specific to Azattyk's website structure,
     * with special handling for lazy loading and multilingual content cleanup.
     *
     * @param string $htmlContent The HTML content from Azattyk's news page
     *
     * @return NewsArticleDto[] Array of parsed news articles
     */
    public function parse(string $htmlContent): array
    {
        $crawler = new Crawler($htmlContent, $this->config['listUrl']);
        $newsItems = [];

        $crawler->filter($this->config['itemSelector'])->each(function (Crawler $node) use (&$newsItems) {
            $title = $this->extractText($node, $this->config['titleSelector']);
            $rawLink = $this->extractAttribute($node, $this->config['linkSelector'], $this->config['linkAttribute']);
            $link = $this->resolveUrl($rawLink);

            // Skip items without title or link
            if (empty($title) || empty($link)) {
                return;
            }

            $rawImageUrl = $this->extractAttribute($node, $this->config['imageSelector'], $this->config['imageAttribute']);
            $imageUrl = $this->resolveUrl($rawImageUrl);

            // Azattyk uses data-src for lazy loading, try if src is empty
            if (empty($imageUrl)) {
                $rawImageUrl = $this->extractAttribute($node, $this->config['imageSelector'], 'data-src');
                $imageUrl = $this->resolveUrl($rawImageUrl);
            }

            // Also check noscript img tag as fallback
            if (empty($imageUrl)) {
                $noscriptImg = $this->extractAttribute($node, 'noscript img', 'src');
                $imageUrl = $this->resolveUrl($noscriptImg);
            }

            $description = $this->extractText($node, $this->config['descriptionSelector']);

            // Clean up description (remove common trailing phrases in Kyrgyz/Russian)
            $description = preg_replace('/\s*(Подробнее|Толугураак|Read more|Толук маалымат)\s*$/iu', '', $description);
            $description = trim($description);

            // Create news item if we have minimum required data
            if ($title && $link) {
                $newsItems[] = new NewsArticleDto($title, $imageUrl, $description, $link);
            }
        });

        return $newsItems;
    }
}
