<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Enums\AccessMode;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Exception\Neo4jException;

class AccessModesIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = new Neo4jQueryAPI();
    }

    public function testRunWithWriteAccessMode(): void
    {
        $result = $this->api->run(
            "CREATE (n:Person {name: 'Alice'}) RETURN n",
            [],
            'neo4j',
            null,
            null,
            AccessMode::WRITE
        );
        $this->assertNotNull($result);
    }

    public function testRunWithReadAccessMode(): void
    {
        $result = $this->api->run(
            "MATCH (n) RETURN COUNT(n)",
            [],
            'neo4j',
            null,
            null,
            AccessMode::READ
        );
        $this->assertNotNull($result);
    }

    public function testReadModeWithWriteQuery(): void
    {
        $this->expectException(Neo4jException::class);
        $this->api->run(
            "CREATE (n:Test {name: 'Test Node'})",
            [],
            'neo4j',
            new Bookmarks([]),
            null,
            AccessMode::READ
        );
    }

    public function testWriteModeWithReadQuery(): void
    {
        $result = $this->api->run(
            "MATCH (n:Test) RETURN n",
            [],
            'neo4j',
            null,
            null,
            AccessMode::WRITE
        );
        $this->assertNotNull($result);
    }
}

