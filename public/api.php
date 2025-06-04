<?php
/**
 * News Parser API Endpoint
 *
 * This API endpoint fetches and parses news articles from various sources.
 * It supports multiple news sources and returns structured JSON data.
 *
 * @param string $source Required GET parameter specifying the news source
 *
 * @return array JSON response containing:
 *   - articles: Array of parsed news articles
 *   - count: Number of articles found
 *   - source: The requested source identifier
 *   - error: Error message (if request fails)
 *
 * @throws RuntimeException When parser configuration is missing or invalid
 * @throws Throwable For any unexpected server errors
 */

// Disable HTML error output to prevent JSON corruption
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Autoload Composer
require __DIR__ . '/../vendor/autoload.php';

use App\Factory\ParserFactory;
use App\Service\HttpClientService;
use GuzzleHttp\Client as GuzzleClient;

// Ensure we only output JSON
ob_start();

try {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $source = $_GET['source'] ?? null;

    if (!$source) {
        http_response_code(400);
        echo json_encode(['error' => 'Source parameter is missing.']);
        exit;
    }

    $configPath = __DIR__ . '/../config/parser_config.php';

    if (!file_exists($configPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Parser configuration file not found at: ' . $configPath]);
        exit;
    }

    $parserConfigs = require $configPath;

    if (!isset($parserConfigs[strtolower($source)])) {
        http_response_code(400);
        echo json_encode(['error' => "Unsupported source: {$source}. Available sources: " . implode(', ', array_keys($parserConfigs))]);
        exit;
    }

    $config = $parserConfigs[strtolower($source)];
    $listUrl = $config['listUrl'];

    $guzzleClient = new GuzzleClient([
        'timeout' => 30,
        'connect_timeout' => 10,
        'verify' => false, // Disable SSL verification
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]
    ]);

    $httpClient = new HttpClientService($guzzleClient);
    $parserFactory = new ParserFactory($parserConfigs);

    $htmlContent = $httpClient->get($listUrl);

    if ($htmlContent === null) {
        http_response_code(500);
        echo json_encode(['error' => "Failed to fetch content from {$source}. The website might be unavailable or blocking requests."]);
        exit;
    }

    if (empty(trim($htmlContent))) {
        http_response_code(500);
        echo json_encode(['error' => "Empty response received from {$source}."]);
        exit;
    }

    $parser = $parserFactory->createParser($source);
    $newsArticles = $parser->parse($htmlContent);

    $outputArticles = array_map(function ($articleDto) {
        return $articleDto->toArray();
    }, $newsArticles);

    ob_clean();

    echo json_encode([
        'articles' => $outputArticles,
        'count' => count($outputArticles),
        'source' => $source
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (RuntimeException $e) {
    ob_clean();
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Throwable $e) {
    ob_clean();
    error_log("API Critical Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'An unexpected error occurred on the server. Check server logs for details.']);
}
?>
