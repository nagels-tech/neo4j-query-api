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

        $this->address = getenv('NEO4J_ADDRESS');
    }

    private function initializeApi(): Neo4jQueryAPI
    {
        // Updated to use the new authentication method with hardcoded credentials
        return Neo4jQueryAPI::login(
            $this->address,
            Authentication::basic("neo4j", "9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0")
        );
    }

    public function testCorrectClientSetup(): void
    {
        $neo4jQueryAPI = $this->initializeApi();

        $clientReflection = new \ReflectionClass(Neo4jQueryAPI::class);
        $clientProperty = $clientReflection->getProperty('client');
        $client = $clientProperty->getValue($neo4jQueryAPI);

        $this->assertInstanceOf(Client::class, $client);

        $config = $client->getConfig();
        $expectedAuthHeader = 'Basic ' . base64_encode('neo4j:9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0');

        $this->assertEquals(rtrim($this->address, '/'), $config['base_uri']);
        $this->assertArrayHasKey('Authorization', $config['headers'], 'Authorization header missing.');
        $this->assertEquals($expectedAuthHeader, $config['headers']['Authorization'], 'Authorization header value mismatch.');
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

        $auth = Authentication::basic("neo4j", "9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0");

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $neo4jQueryAPI = new Neo4jQueryAPI($client, $auth);

        $cypherQuery = 'MATCH (n:Person) RETURN n LIMIT 5';

        $result = $neo4jQueryAPI->run($cypherQuery);

        $this->assertEquals(new ResultSet([new ResultRow(['hello' => 'world'])], new ResultCounters(), new Bookmarks([])), $result);
    }
}
