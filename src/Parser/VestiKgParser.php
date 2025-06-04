<?php

namespace App\Parser;

use App\DTO\NewsArticleDto;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Vesti.kg News Parser
 *
 * Concrete parser implementation for Vesti.kg news source.
 * Handles the specific DOM structure and lazy loading patterns
 * used by the Vesti.kg website, including placeholder image detection.
 *
 * @package App\Parser
 */
class VestiKgParser extends AbstractParser
{
    /**
     * Parses Vesti.kg HTML content and extracts news articles
     * Implements parsing logic specific to Vesti.kg's website structure,
     * with advanced image handling for lazy loading and placeholder detection.
     *
     * @param string $htmlContent The HTML content from Vesti.kg's news page
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

            // Handle image extraction with better lazy loading detection
            $imageUrl = null;

            // First try data-src for lazy loaded images
            $rawImageUrl = $this->extractAttribute($node, $this->config['imageSelector'], 'data-src');
            if (!empty($rawImageUrl) && !$this->isPlaceholderImage($rawImageUrl)) {
                $imageUrl = $this->resolveUrl($rawImageUrl);
            }

            // If data-src is empty or placeholder, try regular src
            if (empty($imageUrl)) {
                $rawImageUrl = $this->extractAttribute($node, $this->config['imageSelector'], 'src');
                if (!empty($rawImageUrl) && !$this->isPlaceholderImage($rawImageUrl)) {
                    $imageUrl = $this->resolveUrl($rawImageUrl);
                }
            }

            // Try data-lazy-src as another fallback
            if (empty($imageUrl)) {
                $rawImageUrl = $this->extractAttribute($node, $this->config['imageSelector'], 'data-lazy-src');
                if (!empty($rawImageUrl) && !$this->isPlaceholderImage($rawImageUrl)) {
                    $imageUrl = $this->resolveUrl($rawImageUrl);
                }
            }

            $description = $this->extractText($node, $this->config['descriptionSelector']);

            // Clean up description (remove common trailing phrases)
            $description = preg_replace('/\s*(Подробнее|Читать далее|Read more)\s*$/iu', '', $description);
            $description = trim($description);

            // Create news item if we have minimum required data
            if ($title && $link) {
                $newsItems[] = new NewsArticleDto($title, $imageUrl, $description, $link);
            }
        });

        return $newsItems;
    }

    /**
     * Checks if the image URL is a placeholder or lazy loading image
     *
     * Identifies common placeholder patterns used in lazy loading implementations
     * to avoid using placeholder images as actual article images.
     *
     * @param string|null $url The image URL to check
     *
     * @return bool True if the URL is identified as a placeholder, false otherwise
     *
     * @since 1.0.0
     *
     * @example
     * $this->isPlaceholderImage('data:image/svg+xml'); // Returns: true
     * $this->isPlaceholderImage('https://example.com/real-image.jpg'); // Returns: false
     */
    private function isPlaceholderImage(?string $url): bool
    {
        if (empty($url)) {
            return true;
        }

        // Check for common placeholder patterns
        $placeholderPatterns = [
            'data:image/svg+xml',  // SVG placeholders
            'placeholder',         // URLs containing "placeholder"
            'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP', // 1x1 transparent GIF
            '/images/spacer.',     // Spacer images
            'blank.gif',           // Blank GIF files
            'transparent.png',     // Transparent PNG files
            '1x1.png',            // 1x1 pixel images
        ];

        foreach ($placeholderPatterns as $pattern) {
            if (stripos($url, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
