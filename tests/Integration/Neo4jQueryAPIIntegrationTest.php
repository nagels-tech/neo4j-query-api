<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class Neo4jQueryAPIIntegrationTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        // Use environment variables from phpunit.xml
        $this->address = getenv('NEO4J_ADDRESS');
        $this->username = getenv('NEO4J_USERNAME');
        $this->password = getenv('NEO4J_PASSWORD');
    }
    public function testRunSuccess(): void
    {
        $api = Neo4jQueryAPI::login('https://bb79fe35.databases.neo4j.io', 'neo4j', 'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0');

// Run the query and fetch results
        $results = $api->run('MATCH (n:Person) RETURN n LIMIT 10',[]);
        $this->assertIsArray($results);
    }
}
