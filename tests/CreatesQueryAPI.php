<?php

namespace Neo4j\QueryAPI\Tests;

use Neo4j\QueryAPI\Configuration;
use Neo4j\QueryAPI\Enums\AccessMode;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Bookmarks;

trait CreatesQueryAPI
{
    protected Neo4jQueryAPI $api;

    protected function createQueryAPI(AccessMode $accessMode = AccessMode::WRITE, ?Bookmarks $bookmarks = null): void
    {
        $neo4jAddress = getenv('NEO4J_ADDRESS');
        if (!is_string($neo4jAddress) || trim($neo4jAddress) === '') {
            throw new \RuntimeException('NEO4J_ADDRESS is not set or is invalid.');
        }

        $this->api = Neo4jQueryAPI::create(
            new Configuration(baseUri: $neo4jAddress, bookmarks: $bookmarks ?? new Bookmarks([]), accessMode: $accessMode),
            Authentication::fromEnvironment()
        );
    }
}
