<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Exception;
use Neo4j\QueryAPI\Objects\Authentication;
use GuzzleHttp\Exception\GuzzleException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Tests\CreatesQueryAPI;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class Neo4jTransactionIntegrationTest extends TestCase
{
    use CreatesQueryAPI;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createQueryAPI();
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

    public function testTransactionRollback(): void
    {
        $tsx = $this->api->beginTransaction();

        $name = 'rollback_' . ((string) mt_rand(1, 100000));
        $tsx->run("CREATE (x:Human {name: \$name})", ['name' => $name]);
        $results = $tsx->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(1, $results);

        $tsx->rollback();

        $results = $this->api->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(0, $results);
    }

    public function testCreateNodeAndCommit(): void
    {
        $tsx = $this->api->beginTransaction();

        $name = 'committed_' . (string) mt_rand(1, 100000);
        $tsx->run("CREATE (x:Person {name: \$name})", ['name' => $name]);

        $results = $this->api->run("MATCH (x:Person {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(0, $results);

        $tsx->commit();
        $results = $this->api->run("MATCH (x:Person {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(1, $results);
    }

    public function testCreateNodeAndRollback(): void
    {
        $tsx = $this->api->beginTransaction();

        $name = 'rollback_' .(string) mt_rand(1, 100000);
        $tsx->run("CREATE (x:Person {name: \$name})", ['name' => $name]);

        $results = $tsx->run("MATCH (x:Person {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(1, $results);

        $results = $this->api->run("MATCH (x:Person {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(0, $results);

        $tsx->rollback();
        $results = $this->api->run("MATCH (x:Person {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(0, $results);
    }



}
