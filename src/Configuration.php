<?php

namespace Neo4j\QueryAPI;

use InvalidArgumentException;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Enums\AccessMode;

class Configuration
{
    /**
     * Constructor for Configuration class.
     *
     * @param string $baseUri     The base URI for the Neo4j instance.
     * @param string $database    The name of the database to connect to.
     * @param bool   $includeCounters Whether to include counters in the response.
     * @param Bookmarks $bookmarks Bookmarks for tracking queries.
     * @param AccessMode $accessMode The access mode for the connection (read/write).
     *
     * @throws InvalidArgumentException if $baseUri is empty.
     */
    public function __construct(
        public readonly string     $baseUri,
        public readonly string     $database = 'neo4j',
        public readonly bool       $includeCounters = true,
        public readonly Bookmarks  $bookmarks = new Bookmarks([]),
        public readonly AccessMode $accessMode = AccessMode::WRITE,
    ) {
        if (empty($this->baseUri)) {
            throw new InvalidArgumentException("Address (baseUri) must be provided.");
        }
    }
}

