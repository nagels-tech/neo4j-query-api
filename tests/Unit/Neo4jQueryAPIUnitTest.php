<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIUnitTest extends TestCase
{
    protected string $address;
    protected string $username;
    protected string $password;

    protected function setUp(): void
    {
        parent::setUp();

        $this->address = getenv('NEO4J_ADDRESS');
        $this->username = getenv('NEO4J_USERNAME');
        $this->password = getenv('NEO4J_PASSWORD');
    }

    public function testCorrectClientSetup(): void
    {
        $neo4jQueryAPI = Neo4jQueryAPI::login($this->address, $this->username, $this->password);

        $this->assertInstanceOf(Neo4jQueryAPI::class, $neo4jQueryAPI);

        $clientReflection = new \ReflectionClass(Neo4jQueryAPI::class);
        $clientProperty = $clientReflection->getProperty('client');
        $client = $clientProperty->getValue($neo4jQueryAPI);

        $this->assertInstanceOf(Client::class, $client);

        $config = $client->getConfig();
        $this->assertEquals(rtrim($this->address, '/'), $config['base_uri']);
        $this->assertEquals('Basic ' . base64_encode("{$this->username}:{$this->password}"), $config['headers']['Authorization']);
        $this->assertEquals('application/vnd.neo4j.query', $config['headers']['Content-Type']);
    }

    /**
     * @throws GuzzleException
     */
    public function testRunSuccess(): void
    {
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
