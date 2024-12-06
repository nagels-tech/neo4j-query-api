<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIUnitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use environment variables from phpunit.xml
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
        $this->assertEquals('application/json', $config['headers']['Content-Type']);
    }

    /**
     * @throws GuzzleException
     */
    public function testRunSuccess(): void
    {

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], '{"hello":"world"}'),
        ]);

        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);

        $neo4jQueryAPI = new Neo4jQueryAPI($client);

        $cypherQuery = 'MATCH (n:Person) RETURN n LIMIT 5';

        $result = $neo4jQueryAPI->run($cypherQuery, []);

      //  print_r($result);

        $this->assertEquals(['hello' => 'world'], $result);
    }
}
