<?php

namespace Neo4j\QueryAPI\Tests\Integration;

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

        $bookmarks = $result->getBookmarks() ?? new Bookmarks([]);

        $result = $this->api->run('CREATE (x:Node {hello: "world2"})');
        $bookmarks->addBookmarks($result->getBookmarks());

        $result = $this->api->run('MATCH (x:Node {hello: "world2"}) RETURN x');
        $bookmarks->addBookmarks($result->getBookmarks());

        $this->assertCount(1, $result);
    }

}
