<?php
namespace Neo4j\QueryAPI\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Results\ResultSet;

class BookmarksIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = new Neo4jQueryAPI(/* pass necessary config */);
    }

    public function testCreateBookmarks(): void
    {
        $result1 = $this->api->run('CREATE (x:Node {hello: "world"})');
        $bookmarks = $result1->getBookmarks() ?? new Bookmarks([]);

        $result2 = $this->api->run('CREATE (x:Node {hello: "world2"})');
        $bookmarks->addBookmarks($result2->getBookmarks()->getBookmarks());

        $result3 = $this->api->run('MATCH (x:Node {hello: "world2"}) RETURN x');

        $this->assertCount(1, iterator_to_array($result3));
    }
}
