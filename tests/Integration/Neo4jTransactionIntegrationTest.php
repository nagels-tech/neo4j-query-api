<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Exception;
use Neo4j\QueryAPI\Objects\Authentication;
use GuzzleHttp\Exception\GuzzleException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @api
 */
class Neo4jTransactionIntegrationTest extends TestCase
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private Neo4jQueryAPI $api;

    /**
     * @throws GuzzleException
     */
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $address = is_string(getenv('NEO4J_ADDRESS')) ? getenv('NEO4J_ADDRESS') : '';

        if ($address === '') {
            throw new RuntimeException('NEO4J_ADDRESS is not set.');
        }

        $this->api = $this->initializeApi();
        $this->clearDatabase();
        $this->populateTestData();
    }

    /**
     * @throws Exception
     */
    private function initializeApi(): Neo4jQueryAPI
    {
        $address = getenv('NEO4J_ADDRESS');

        if ($address === false) {
            throw new RuntimeException('NEO4J_ADDRESS is not set in the environment.');
        }

        return Neo4jQueryAPI::login($address, Authentication::fromEnvironment());
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
        $names = ['bob1', 'alice'];
        foreach ($names as $name) {
            $this->api->run('CREATE (:Person {name: $name})', ['name' => $name]);
        }
    }

    //    public function testTransactionCommit(): void
    //    {
    //        // Begin a transaction
    //        $tsx = $this->api->beginTransaction();
    //
    //        // Generate a unique name
    //        $name = (string) mt_rand(1, 100000);
    //
    //        // Run a query within the transaction
    //        $tsx->run("CREATE (x:Human {name: \$name})", ['name' => $name]);
    //
    //        // Verify the record is not yet committed
    //        $results = $this->api->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
    //        $this->assertCount(0, $results, 'Record should not exist before commit.');
    //
    //        // Run the same query within the transaction and verify it's in the transaction
    //        $results = $tsx->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
    //        $this->assertCount(1, $results, 'Record should exist within the transaction.');
    //
    //        // Commit the transaction
    //        $tsx->commit();
    //
    //        // Verify the record is now committed
    //        $results = $this->api->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
    //        $this->assertCount(1, $results, 'Record should exist after commit.');
    //    }
}
