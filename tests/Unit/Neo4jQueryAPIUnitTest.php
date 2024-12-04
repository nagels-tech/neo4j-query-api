<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIUnitTest extends TestCase
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

    public function testCorrectClientSetup(): void
    {
        $neo4jQueryAPI = Neo4jQueryAPI::login($this->address, $this->username, $this->password);

        $this->assertInstanceOf(Neo4jQueryAPI::class, $neo4jQueryAPI);

        $clientReflection = new \ReflectionClass(Neo4jQueryAPI::class);
        $clientProperty = $clientReflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $client = $clientProperty->getValue($neo4jQueryAPI);

        $this->assertInstanceOf(Client::class, $client);

        $config = $client->getConfig();
        $this->assertEquals(rtrim($this->address, '/'), $config['base_uri']);
        $this->assertEquals('Basic ' . base64_encode("{$this->username}:{$this->password}"), $config['headers']['Authorization']);
        $this->assertEquals('application/json', $config['headers']['Content-Type']);
    }

    public function testRunSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], '{"hello":"world"}'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $neo4jQueryAPI = new Neo4jQueryAPI($client);


        $cypherQuery = 'MATCH (n:Person) RETURN n LIMIT 5';
        $result = $neo4jQueryAPI->run($cypherQuery);

        print_r($result);
        $this->assertEquals(['hello' => 'world'], $result);
    }
}
