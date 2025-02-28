<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use http\Client;
use Http\Discovery\Psr17FactoryDiscovery;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Neo4jRequestFactory;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\ResponseParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Neo4j\QueryAPI\Configuration;

final class Neo4jQueryAPIUnitTest extends TestCase
{
    protected string $address;

    protected ResponseParser $parser;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $address = getenv('NEO4J_ADDRESS');
        $this->address = is_string($address) ? $address : '';

        $this->parser = new ResponseParser(new OGM());
    }

    public function testCorrectClientSetup(): void
    {
        $neo4jQueryAPI = Neo4jQueryAPI::login($this->address, Authentication::fromEnvironment());
        $this->assertInstanceOf(Neo4jQueryAPI::class, $neo4jQueryAPI);
    }

    #[DoesNotPerformAssertions]
    public function testRunSuccess(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"data": {"fields": ["hello"], "values": [[{"$type": "String", "_value": "world"}]]}}')
        ]);

        $handler = HandlerStack::create($mockHandler);
        $client = new \GuzzleHttp\Client(['handler' => $handler]);

        $neo4jQueryAPI = new Neo4jQueryAPI(
            $client,
            $this->parser,
            new Neo4jRequestFactory(
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
                new Configuration($this->address),
                Authentication::fromEnvironment()
            ),
            new Configuration($this->address)
        );

        $neo4jQueryAPI->run('MATCH (n:Person) RETURN n LIMIT 5');
    }

    public function testParseValidResponse(): void
    {
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn(json_encode([
            'data' => ['fields' => ['name'], 'values' => [['Alice'], ['Bob']]],
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
            'data' => ['fields' => [], 'values' => []],
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
            'data' => ['fields' => [], 'values' => []],
            'bookmarks' => ['bm1', 'bm2', 'bm3']
        ]));

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($mockStream);

        $result = $this->parser->parseRunQueryResponse($mockResponse);
        $this->assertInstanceOf(ResultSet::class, $result);

        $bookmarks = $result->bookmarks;
        $this->assertInstanceOf(Bookmarks::class, $bookmarks);
        $this->assertCount(3, $bookmarks->bookmarks);
        $this->assertEquals(['bm1', 'bm2', 'bm3'], $bookmarks->bookmarks);
    }
}
