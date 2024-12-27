<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Transaction;
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
        $neo4jQueryAPI = Transaction::login($this->address, $this->username, $this->password);

        $this->assertInstanceOf(Transaction::class, $neo4jQueryAPI);

        $clientReflection = new \ReflectionClass(Transaction::class);
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
            new Response(200, ['X-Foo' => 'Bar'], '{"hello":"world"}'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $neo4jQueryAPI = new Transaction($client);

        $cypherQuery = 'MATCH (n:Person) RETURN n LIMIT 5';


        $result = $neo4jQueryAPI->run($cypherQuery, []);


        $this->assertEquals(['hello' => 'world'], $result);
    }
}
