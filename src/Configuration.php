<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Enums\AccessMode;

final class Configuration
{
    public function __construct(
        public readonly string     $baseUri,
        public readonly string     $database = 'neo4j',
        public readonly bool       $includeCounters = true,
        public readonly Bookmarks  $bookmarks = new Bookmarks([]),
        public readonly AccessMode $accessMode = AccessMode::WRITE,
    ) {
    }
}
