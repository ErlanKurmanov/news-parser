<?php

namespace App\Contract;

use App\DTO\NewsArticleDto;

/**
 * Parser Interface
 *
 * Defines the contract for all news source parsers.
 * Each parser implementation must provide a parse method that extracts
 * news articles from HTML content.
 */
interface ParserInterface
{
    /**
     * Parses the given HTML content and extracts news articles.
     *
     * This method should extract structured news data from raw HTML content
     * and return an array of NewsArticleDto objects containing the parsed information.
     *
     * @param string $htmlContent The HTML content to parse from the news source
     *
     * @return NewsArticleDto[] An array of NewsArticleDto objects containing parsed news articles
     *
     * @throws \InvalidArgumentException When HTML content is invalid or empty
     * @throws \RuntimeException When parsing fails due to structural changes in the source
     *
     * @since 1.0.0
     */
    public function parse(string $htmlContent): array;
}
