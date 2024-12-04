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
    private string $address;
    private string $username;
    private string $password;

    protected function setUp(): void
    {
        parent::setUp();
        $this->address = 'https://bb79fe35.databases.neo4j.io';
        $this->username = 'neo4j';
        $this->password = 'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0';
    }
    public function testRunSuccess(): void
    {
        $api = Neo4jQueryAPI::login('https://bb79fe35.databases.neo4j.io', 'neo4j', 'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0');

// Run the query and fetch results
        $results = $api->run('MATCH (n:Person) RETURN n LIMIT 10');
        $this->assertIsArray($results);
    }
}
