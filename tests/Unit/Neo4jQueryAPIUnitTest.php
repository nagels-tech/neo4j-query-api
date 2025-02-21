<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Neo4jRequestFactory;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\Results\ResultSet;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\ResponseParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Neo4j\QueryAPI\Configuration;

/**
 *  @api
 */
class Neo4jQueryAPIUnitTest extends TestCase
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private OGM $ogm;

    /** @psalm-suppress PropertyNotSetInConstructor */
    protected string $address;

    /** @psalm-suppress PropertyNotSetInConstructor */
    protected ResponseParser $parser;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $address = getenv('NEO4J_ADDRESS');
        $this->address = is_string($address) ? $address : '';

        $this->ogm = new OGM();
        $this->parser = new ResponseParser($this->ogm);
    }





    public function testCorrectClientSetup(): void
    {
        $neo4jQueryAPI = Neo4jQueryAPI::login($this->address, Authentication::fromEnvironment());
        $this->assertInstanceOf(Neo4jQueryAPI::class, $neo4jQueryAPI);
    }

    #[DoesNotPerformAssertions]
    public function testRunSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '{"data": {"fields": ["hello"], "values": [[{"$type": "String", "_value": "world"}]]}}'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $loginConfig = Authentication::fromEnvironment();
        $queryConfig = new Configuration($this->address);

        $responseParser = $this->createMock(ResponseParser::class);

        $neo4jQueryAPI = new Neo4jQueryAPI($client, $responseParser, new Neo4jRequestFactory(
            new Psr17Factory(),
            new Psr17Factory(),
            $queryConfig,
            $loginConfig
        ));

        $cypherQuery = 'MATCH (n:Person) RETURN n LIMIT 5';
        $neo4jQueryAPI->run($cypherQuery);
    }



    public function testParseValidResponse(): void
    {
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn(json_encode([
            'data' => [
                'fields' => ['name'],
                'values' => [['Alice'], ['Bob']],
            ],
            'counters' => ['nodesCreated' => 2],
            'bookmarks' => ['bm1'],
            'accessMode' => 'WRITE'
        ]));

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($mockStream);

        $result = $this->parser->parseRunQueryResponse($mockResponse);
        $this->assertInstanceOf(ResultSet::class, $result);
        $this->assertCount(2, $result->getIterator());
    }

    public function testParseInvalidResponse(): void
    {
        $this->expectException(RuntimeException::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn(json_encode(['data' => null]));

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($mockStream);

        $this->parser->parseRunQueryResponse($mockResponse);
    }

    public function testGetAccessMode(): void
    {
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn(json_encode([
            'data' => [
                'fields' => [],
                'values' => []
            ],
            'accessMode' => 'WRITE'
        ]));

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($mockStream);

        $result = $this->parser->parseRunQueryResponse($mockResponse);
        $this->assertInstanceOf(ResultSet::class, $result);
    }
    public function testParseBookmarks(): void
    {
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn(json_encode([
            'data' => [
                'fields' => [],
                'values' => []
            ],
            'bookmarks' => ['bm1', 'bm2', 'bm3']
        ]));

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($mockStream);

        $result = $this->parser->parseRunQueryResponse($mockResponse);

        $this->assertInstanceOf(ResultSet::class, $result);

        $bookmarks = $result->getBookmarks();

        $this->assertInstanceOf(Bookmarks::class, $bookmarks);
        $this->assertCount(3, $bookmarks->getBookmarks());
        $this->assertEquals(['bm1', 'bm2', 'bm3'], $bookmarks->getBookmarks());
    }

}
