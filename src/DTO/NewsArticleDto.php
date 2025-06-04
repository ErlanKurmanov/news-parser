<?php

namespace App\DTO;

/**
 * News Article Data Transfer Object
 *
 * Represents a single news article with its essential properties.
 * This DTO is used to transfer news article data between different
 * layers of the application.
 */
class NewsArticleDto
{
    /**
     * The title/headline of the news article
     */
    public string $title;

    /**
     * URL of the article's featured image
     */
    public ?string $imageUrl;

    /**
     * Brief description or excerpt of the article
     */
    public string $description;

    /**
     * URL linking to the full article on the source website
     */
    public string $sourceUrl;

    /**
     * Constructor for NewsArticleDto
     *
     * @param string      $title       The article title/headline
     * @param string|null $imageUrl    URL of the article's image
     * @param string      $description Brief description
     * @param string      $sourceUrl   URL to the full article
     */
    public function __construct(string $title, ?string $imageUrl, string $description, string $sourceUrl)
    {
        $this->title = $title;
        $this->imageUrl = $imageUrl;
        $this->description = $description;
        $this->sourceUrl = $sourceUrl;
    }

    /**
     * Converts the DTO to an associative array.
     *
     * This method is useful for JSON serialization and API responses.
     *
     * @return array<string, string|null> Associative array representation of the article
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'imageUrl' => $this->imageUrl,
            'description' => $this->description,
            'sourceUrl' => $this->sourceUrl,
        ];
    }
}
