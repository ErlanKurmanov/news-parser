<?php

namespace App\Parser;

use App\DTO\NewsArticleDto;
use Symfony\Component\DomCrawler\Crawler;

/**
 * RT News Parser
 *
 * Concrete parser implementation for RT (Russia Today) news source.
 * Handles the specific DOM structure and content extraction patterns
 * used by the RT website.
 *
 * @package App\Parser
 */
class RtParser extends AbstractParser
{
    /**
     * Parses RT HTML content and extracts news articles
     *
     * Implements the parsing logic specific to RT's website structure,
     * including handling of lazy-loaded images and Russian text cleanup.
     *
     * @param string $htmlContent The HTML content from RT's news page
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

            // RT sometimes uses data-src for lazy loading
            if (empty($imageUrl) && $this->config['imageSelector'] === 'picture.card__cover-picture img') {
                $rawImageUrl = $this->extractAttribute($node, $this->config['imageSelector'], 'data-src');
                $imageUrl = $this->resolveUrl($rawImageUrl);
            }

            $description = $this->extractText($node, $this->config['descriptionSelector']);

            // Clean description from "Read more" and extra spaces
            $description = preg_replace('/\s*Read more\s*$/i', '', $description);
            $description = trim($description);

            if ($title && $link) {
                $newsItems[] = new NewsArticleDto($title, $imageUrl, $description, $link);
            }
        });

        return $newsItems;
    }
}
