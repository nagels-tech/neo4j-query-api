<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Configuration;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Tests\CreatesQueryAPI;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Exception\InvalidBookmarkException;

final class BookmarksIntegrationTest extends TestCase
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


    public function testInvalidBookmarkThrowsException(): void
    {
        $exceptionCaught = false;

        $invalidBookmark = new Bookmarks(['invalid:bookmark']);
        $this->createQueryAPI(bookmarks: $invalidBookmark);

        try {
            $this->api->run('MATCH (n) RETURN n');
        } catch (Neo4jException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Parsing of supplied bookmarks failed with message: Illegal base64 character 3a', $e->getMessage());
            $this->assertEquals('InvalidBookmark', $e->getName());
            $this->assertEquals('Transaction', $e->getSubType());
            $this->assertEquals('ClientError', $e->getType());
        }

        $this->assertTrue($exceptionCaught);
    }


}
