<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\Tests\CreatesQueryAPI;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Objects\Bookmarks;

class BookmarksIntegrationTest extends TestCase
{
    use CreatesQueryAPI;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createQueryAPI();
    }


    public function testCreateBookmarks(): void
    {
        $result = $this->api->run('CREATE (x:Node {hello: "world"})');

        $bookmarks = $result->bookmarks ?? new Bookmarks([]);

        $result = $this->api->run('CREATE (x:Node {hello: "world2"})');
        $bookmarks->addBookmarks($result->bookmarks);

        $result = $this->api->run('MATCH (x:Node {hello: "world2"}) RETURN x');
        $bookmarks->addBookmarks($result->bookmarks);

        $this->assertCount(1, $result);
    }

}
