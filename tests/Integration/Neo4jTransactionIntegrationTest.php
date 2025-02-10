<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Exception;
use Neo4j\QueryAPI\Objects\Authentication;
use GuzzleHttp\Exception\GuzzleException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\TestCase;

class Neo4jTransactionIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    /**
     * @throws GuzzleException
     */
    public function setUp(): void
    {
        $this->api = $this->initializeApi();
        $this->clearDatabase();
        $this->populateTestData();
    }

    /**
     * @throws Exception
     */
    private function initializeApi(): Neo4jQueryAPI
    {
        return Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS'),
            Authentication::fromEnvironment(),
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

        $tsx = $this->api->beginTransaction();

        $name = (string)mt_rand(1, 100000);
        $tsx->run("CREATE (x:Human {name: \$name})", ['name' => $name]);

        $results = $this->api->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(0, $results);

        $results = $tsx->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(1, $results);

        $tsx->commit();
        $results = $this->api->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(1, $results); // Updated to expect 1 result
    }

}
