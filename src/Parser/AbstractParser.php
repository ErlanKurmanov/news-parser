<?php

namespace App\Parser;

use App\Contract\ParserInterface;
use App\DTO\NewsArticleDto;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Abstract Parser Base Class
 *
 * Provides common functionality for all news source parsers.
 * Contains utility methods for DOM manipulation, URL resolution,
 * and text extraction that are shared across different parser implementations.
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * Configuration array for the specific news source
     *
     * Contains selectors, URLs, and other settings needed for parsing
     */
    protected array $config;

    /**
     * Constructor for AbstractParser
     *
     * @param array<string, mixed> $config Configuration array for the news source
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Abstract method that must be implemented by concrete parsers
     *
     * @param string $htmlContent The HTML content to parse
     *
     * @return NewsArticleDto[] Array of parsed news articles
     */
    abstract public function parse(string $htmlContent): array;

    /**
     * Extracts text content from a DOM element using CSS selector
     *
     * @param Crawler $node     The DOM crawler node to search within
     * @param string  $selector CSS selector to find the target element
     *
     * @return string The extracted text content, or empty string if not found
     */
    protected function extractText(Crawler $node, string $selector): string
    {
        $element = $node->filter($selector);
        return $element->count() > 0 ? trim($element->text('')) : '';
    }

    /**
     * Extracts an attribute value from a DOM element using CSS selector
     *
     * @param Crawler $node      The DOM crawler node to search within
     * @param string  $selector  CSS selector to find the target element
     * @param string  $attribute The attribute name to extract
     *
     * @return string|null The attribute value, or null if not found
     */
    protected function extractAttribute(Crawler $node, string $selector, string $attribute): ?string
    {
        $element = $node->filter($selector);
        if ($element->count() > 0) {
            $value = $element->attr($attribute);
            return $value ? trim($value) : null;
        }
        return null;
    }

    /**
     * Resolves relative URLs to absolute URLs using the configured base URL
     *
     * Handles various URL formats:
     * - Absolute URLs (http/https) - returned as-is
     * - Protocol-relative URLs (//) - prefixed with https:
     * - Relative paths - combined with base URL
     *
     * @param string|null $url The URL to resolve (can be relative or absolute)
     *
     * @return string|null The resolved absolute URL, or null if input is empty
     */
    protected function resolveUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        $baseUrl = rtrim($this->config['baseUrl'], '/');
        $path = ltrim($url, '/');
        return $baseUrl . '/' . $path;
    }
}
