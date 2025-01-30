<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Neo4jRequestFactory;
use Neo4j\QueryAPI\Objects\Authentication;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\ResultSet;
use Neo4j\QueryAPI\Results\ResultRow;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\RequestExceptionInterface;

class Neo4jTransactionIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    private Neo4jRequestFactory $request;

    /**
     * @throws GuzzleException
     */
    public function setUp(): void
    {
        $this->api = $this->initializeApi();
        // Clear database and populate test data
        $this->clearDatabase();
        $this->populateTestData();
    }

    private function initializeApi(): Neo4jQueryAPI
    {
        return Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS'),
            Authentication::basic("neo4j", "9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0"),
        );
    }

    /**
     * @throws GuzzleException
     */
    private function clearDatabase(): void
    {
        $this->api->run('MATCH (n) DETACH DELETE n', []);
    }

    /**
     * @throws GuzzleException
     */
    private function populateTestData(): void
    {
        $names = ['bob1', 'alicy'];
        foreach ($names as $name) {
            $this->api->run('CREATE (:Person {name: $name})', ['name' => $name]);
        }
    }
    public function testTransactionCommit(): void
    {
        // Begin a new transaction
        $tsx = $this->api->beginTransaction();

        // Generate a random name for the node
        $name = (string)mt_rand(1, 100000);

        // Create a node within the transaction
        $tsx->run("CREATE (x:Human {name: \$name})", ['name' => $name]);

        // Validate that the node does not exist in the database before the transaction is committed
        $results = $this->api->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(0, $results);

        // Validate that the node exists within the transaction
        $results = $tsx->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(1, $results);

        // Commit the transaction
        $tsx->commit();

        // Validate that the node now exists in the database
        $results = $this->api->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(1, $results); // Updated to expect 1 result
    }

}
