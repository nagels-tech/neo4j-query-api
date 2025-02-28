<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Enums\AccessMode;

final class Neo4jQueryAPIIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->api = $this->initializeApi();
        $this->clearDatabase();
        $this->populateTestData();
    }


    public function testParseRunQueryResponse(): void
    {
        $query = 'CREATE (n:TestNode {name: "Test"}) RETURN n';
        $response = $this->api->run($query);
        $bookmarks = $response->getBookmarks() ?? new Bookmarks([]);

        $this->assertEquals(new ResultSet(
            rows: [
                new ResultRow([
                    'n' => new Node(
                        ['TestNode'],
                        ['name' => 'Test']
                    )
                ])
            ],
            counters: new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1
            ),
            bookmarks: $bookmarks,
            profiledQueryPlan: null,
            accessMode: AccessMode::WRITE
        ), $response);
    }

    public function testInvalidQueryHandling(): void
    {
        $this->expectException(Neo4jException::class);
        $this->api->run('INVALID CYPHER QUERY');
    }

    private function initializeApi(): Neo4jQueryAPI
    {
        $address = getenv('NEO4J_ADDRESS');
        if ($address === false) {
            $address = 'default-address';
        }
        return Neo4jQueryAPI::login($address, Authentication::fromEnvironment());
    }
    public function testCounters(): void
    {
        $result = $this->api->run('CREATE (x:Node {hello: "world"})');
        $queryCounters = $result->getQueryCounters();

        $this->assertNotNull($queryCounters);
        $this->assertEquals(1, $queryCounters->getNodesCreated());
    }

    private function clearDatabase(): void
    {
        $this->api->run('MATCH (n) DETACH DELETE n', []);
    }

    private function populateTestData(): void
    {
        $names = ['bob1', 'alicy'];
        foreach ($names as $name) {
            $this->api->run('CREATE (:Person {name: $name})', ['name' => $name]);
        }
    }

    public function testInvalidQueryException(): void
    {
        try {
            $this->api->run('CREATE (:Person {createdAt: $invalidParam})', [
                'date' => new \DateTime('2000-01-01 00:00:00')
            ]);
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Neo4jException::class, $e);
            $this->assertEquals('Neo.ClientError.Statement.ParameterMissing', $e->getErrorCode());
            $this->assertEquals('Expected parameter(s): invalidParam', $e->getMessage());
        }
    }

}
