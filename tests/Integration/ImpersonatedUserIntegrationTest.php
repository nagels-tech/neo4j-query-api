<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Exception\Neo4jException;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Bookmarks;

class ImpersonatedUserIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    public function testImpersonatedUserSuccess(): void
    {
        $this->markTestSkipped("stuck");

        $result = $this->api->run(
            "PROFILE MATCH (n:Person {name: 'Alice'}) RETURN n.name",
            [],
            'neo4j',
            new Bookmarks([]),
            'HAPPYBDAY'
        );

        $impersonatedUser = $result->getImpersonatedUser();
        $this->assertNotNull($impersonatedUser, "Impersonated user should not be null.");
    }

    public function testImpersonatedUserFailure(): void
    {
        $this->markTestSkipped("stuck");
        $this->expectException(Neo4jException::class);

        $this->api->run(
            "PROFILE MATCH (n:Person {name: 'Alice'}) RETURN n.name",
            [],
            'neo4j',
            null,
            'invalidUser'
        );
    }
}
