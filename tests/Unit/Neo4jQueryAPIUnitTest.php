<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\ResultSet;
use Neo4j\QueryAPI\Results\ResultRow;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIUnitTest extends TestCase
{
    protected string $address;
    protected string $username;
    protected string $password;

    protected function setUp(): void
    {
        parent::setUp();

        $this->address = getenv('NEO4J_ADDRESS') ;
        $this->username = getenv('NEO4J_USERNAME') ;
        $this->password = getenv('NEO4J_PASSWORD') ;
    }

    public function testCorrectClientSetup(): void
    {
        // Verify Authentication object creation
        $authentication = Authentication::request($this->username, $this->password);
        $expectedAuthHeader = 'Basic ' . base64_encode("{$this->username}:{$this->password}");
        $this->assertEquals($expectedAuthHeader, $authentication->getHeader(), 'Authentication header mismatch.');

        // Use the updated login method
        $neo4jQueryAPI = Neo4jQueryAPI::login($this->address, $authentication);

        $this->assertInstanceOf(Neo4jQueryAPI::class, $neo4jQueryAPI);

        // Use reflection to access private `client` property
        $clientReflection = new \ReflectionClass(Neo4jQueryAPI::class);
        $clientProperty = $clientReflection->getProperty('client');
        // Ensure we can access private properties
        $client = $clientProperty->getValue($neo4jQueryAPI);

        $this->assertInstanceOf(Client::class, $client);

        // Get the client's configuration and check headers
        $config = $client->getConfig();
        $this->assertEquals(rtrim($this->address, '/'), $config['base_uri']);
        $this->assertArrayHasKey('Authorization', $config['headers'], 'Authorization header missing.');
        $this->assertEquals($expectedAuthHeader, $config['headers']['Authorization'], 'Authorization header value mismatch.');
        $this->assertEquals('application/vnd.neo4j.query', $config['headers']['Content-Type']);
        $this->assertEquals('application/vnd.neo4j.query', $config['headers']['Accept']);
    }


    /**
     * @throws GuzzleException
     */
    public function testRunSuccess(): void
    {
        // Mock response for a successful query
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], '{"data": {"fields": ["hello"], "values": [[{"$type": "String", "_value": "world"}]]}}'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $neo4jQueryAPI = new Neo4jQueryAPI($client);

        $cypherQuery = 'MATCH (n:Person) RETURN n LIMIT 5';

        $result = $neo4jQueryAPI->run($cypherQuery);

        $this->assertEquals(new ResultSet([new ResultRow(['hello' => 'world'])], new ResultCounters(), new Bookmarks([])), $result);
    }
}
