<?php

namespace App\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * HTTP Client Service
 *
 * Service class that wraps Guzzle HTTP client functionality
 * with error handling and logging for news source requests.
 * Provides a simplified interface for making HTTP GET requests.
 */
class HttpClientService
{
    /**
     * The underlying HTTP client instance
     *
     * @var ClientInterface
     */
    private ClientInterface $client;

    /**
     * Constructor for HttpClientService
     *
     * @param ClientInterface $client The HTTP client instance to use for requests
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Performs an HTTP GET request to the specified URL
     *
     * Makes a GET request with appropriate headers and timeout settings.
     * Handles request exceptions and logs errors for debugging purposes.
     *
     * @param string $url The URL to fetch content from
     *
     * @return string|null The response body content, or null if request fails
     */
    public function get(string $url): ?string
    {
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ],
                'timeout' => 10,
                'connect_timeout' => 5
            ]);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            error_log("HTTP request failed for $url: " . $e->getMessage());
            return null;
        }
    }
}
