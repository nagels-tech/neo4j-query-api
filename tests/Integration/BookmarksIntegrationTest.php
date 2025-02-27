<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Configuration;
use Neo4j\QueryAPI\Objects\Authentication;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Results\ResultSet;

final class BookmarksIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $neo4jAddress = getenv('NEO4J_ADDRESS');
        if (!is_string($neo4jAddress) || trim($neo4jAddress) === '') {
            throw new \RuntimeException('NEO4J_ADDRESS is not set or is invalid.');
        }

        $this->api = Neo4jQueryAPI::create(
            new Configuration(baseUri: $neo4jAddress),
            Authentication::fromEnvironment()
        );
    }


    public function testCreateBookmarks(): void
    {
        $result = $this->api->run('CREATE (x:Node {hello: "world"})');

        $bookmarks = $result->getBookmarks() ?? new Bookmarks([]);

        $result = $this->api->run('CREATE (x:Node {hello: "world2"})');
        $bookmarks->addBookmarks($result->getBookmarks());

        $result = $this->api->run('MATCH (x:Node {hello: "world2"}) RETURN x');
        $bookmarks->addBookmarks($result->getBookmarks());

        $this->assertCount(1, $result);
    }

}
