<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\ResultSet;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\AuthenticateInterface;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIUnitTest extends TestCase
{
    protected string $address;

    protected function setUp(): void
    {
        parent::setUp();
        $this->address = getenv('NEO4J_ADDRESS');
    }

    private function initializeApi(): Neo4jQueryAPI
    {
        return Neo4jQueryAPI::login(
            $this->address,
            Authentication::fromEnvironment()
        );
    }

    public function testCorrectClientSetup(): void
    {

        $neo4jQueryAPI = $this->initializeApi();

        $clientReflection = new \ReflectionClass(Neo4jQueryAPI::class);


        $clientProperty = $clientReflection->getProperty('client');
        $client = $clientProperty->getValue($neo4jQueryAPI);
        $this->assertInstanceOf(Client::class, $client);

        $authProperty = $clientReflection->getProperty('auth');
        $auth = $authProperty->getValue($neo4jQueryAPI);
        $this->assertInstanceOf(AuthenticateInterface::class, $auth);


        $expectedAuth = Authentication::fromEnvironment();
        $this->assertEquals($expectedAuth->getHeader(), $auth->getHeader(), 'Authentication headers mismatch');

        $request = new Request('GET', '/test-endpoint');
        $authenticatedRequest = $auth->authenticate($request);


        $expectedAuthHeader = 'Basic ' . base64_encode(getenv("NEO4J_USERNAME") . ':' . getenv("NEO4J_PASSWORD"));
        $this->assertEquals($expectedAuthHeader, $authenticatedRequest->getHeaderLine('Authorization'));

        $requestWithHeaders = $authenticatedRequest->withHeader('Content-Type', 'application/vnd.neo4j.query');
        $this->assertEquals('application/vnd.neo4j.query', $requestWithHeaders->getHeaderLine('Content-Type'));
    }

    /**
     * @throws GuzzleException
     */
    public function testRunSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], '{"data": {"fields": ["hello"], "values": [[{"$type": "String", "_value": "world"}]]}}'),
        ]);

        $auth = Authentication::fromEnvironment();
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $neo4jQueryAPI = new Neo4jQueryAPI($client, $auth);

        $cypherQuery = 'MATCH (n:Person) RETURN n LIMIT 5';
        $result = $neo4jQueryAPI->run($cypherQuery);

        $expectedResult = new ResultSet(
            [new ResultRow(['hello' => 'world'])],
            new ResultCounters(),
            new Bookmarks([])
        );

        $this->assertEquals($expectedResult, $result);
    }
}
