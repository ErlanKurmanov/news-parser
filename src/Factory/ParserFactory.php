<?php

namespace App\Factory;

use App\Contract\ParserInterface;
use App\Parser\RtParser;
use App\Parser\VestiKgParser;
use App\Parser\AzattykParser;
use RuntimeException;

/**
 * Parser Factory
 *
 * Factory class responsible for creating appropriate parser instances
 * based on the news source identifier. Uses configuration array to
 * determine available parsers and their settings.
 */
class ParserFactory
{
    /**
     * Configuration array containing parser settings for each news source
     *
     * @var array<string, array<string, mixed>>
     */
    private array $configurations;

    /**
     * Constructor for ParserFactory
     *
     * @param array<string, array<string, mixed>> $configurations Parser configurations indexed by source identifier
     */
    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * Creates a parser instance for the specified news source
     *
     * @param string $sourceIdentifier The identifier of the news source (e.g., 'rt', 'vesti', 'azattyk')
     *
     * @return ParserInterface The appropriate parser instance for the source
     *
     * @throws RuntimeException When configuration for the source is not found
     * @throws RuntimeException When parser for the source is not implemented
     */
    public function createParser(string $sourceIdentifier): ParserInterface
    {
        $sourceKey = strtolower($sourceIdentifier);

        if (!isset($this->configurations[$sourceKey])) {
            throw new RuntimeException("Configuration for source '{$sourceIdentifier}' not found.");
        }

        $config = $this->configurations[$sourceKey];

        switch ($sourceKey) {
            case 'rt':
                return new RtParser($config);
            case 'vesti':
                return new VestiKgParser($config);
            case 'azattyk':
                return new AzattykParser($config);
            // Add new parsers here
            default:
                throw new RuntimeException("Parser for source '{$sourceIdentifier}' not implemented.");
        }
    }
}
