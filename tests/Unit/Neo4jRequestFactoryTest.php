<?php

namespace Neo4j\QueryAPI\Tests\Unit;

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Neo4jRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Utils;

class Neo4jRequestFactoryTest extends TestCase
{
    private $psr17Factory;
    private $baseUri = 'http://localhost:7474';
    private $authHeader = 'Basic dXNlcjpwYXNzd29yZA==';

    protected function setUp(): void
    {
        $this->psr17Factory = $this->createMock(RequestFactoryInterface::class);
    }

    public function testBuildRunQueryRequest()
    {
        $cypher = 'MATCH (n) RETURN n';
        $parameters = ['param1' => 'value1'];
        $database = 'neo4j';

        $mockRequest = $this->createMock(RequestInterface::class);
        $mockRequest->method('getMethod')->willReturn('POST');

        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->method('__toString')->willReturn('/db/neo4j/query/v2');

        $mockRequest->method('getUri')->willReturn($mockUri);

        $mockStream = Utils::streamFor(json_encode([
            'statement' => $cypher,
            'parameters' => $parameters,
            'includeCounters' => true,
        ]));
        $mockRequest->method('getBody')->willReturn($mockStream);

        $this->psr17Factory->method('createRequest')
            ->willReturn($mockRequest);

        $factory = new Neo4jRequestFactory($this->baseUri, $this->authHeader);
        $request = $factory->buildRunQueryRequest($database, $cypher, $parameters);

        $this->assertEquals('POST', $request['method']);
        $this->assertEquals("{$this->baseUri}/db/{$database}/query/v2", (string) $request['uri']);
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'statement' => $cypher,
                'parameters' => $parameters,
                'includeCounters' => true,
            ]),
            $request['body']
        );
    }

    public function testBuildBeginTransactionRequest()
    {
        $database = 'neo4j';

        $mockRequest = $this->createMock(RequestInterface::class);
        $mockRequest->method('getMethod')->willReturn('POST');

        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->method('__toString')->willReturn('/db/neo4j/query/v2/tx');

        $mockRequest->method('getUri')->willReturn($mockUri);

        $mockStream = Utils::streamFor('');
        $mockRequest->method('getBody')->willReturn($mockStream);

        $this->psr17Factory->method('createRequest')
            ->willReturn($mockRequest);

        $factory = new Neo4jRequestFactory($this->baseUri);
        $request = $factory->buildBeginTransactionRequest($database);

        $this->assertEquals('POST', $request['method']);
        $this->assertEquals("{$this->baseUri}/db/{$database}/query/v2/tx", (string) $request['uri']);
    }

    public function testAuthorizationHeader()
    {
        $factory = new Neo4jRequestFactory($this->baseUri, $this->authHeader);
        $request = $factory->buildRunQueryRequest('neo4j', 'MATCH (n) RETURN n');

        $this->assertArrayHasKey('Authorization', $request['headers']);
        $this->assertEquals($this->authHeader, $request['headers']['Authorization']);
    }

    public function testBuildCommitRequest()
    {
        $database = 'neo4j';
        $transactionId = '12345';

        $mockRequest = $this->createMock(RequestInterface::class);
        $mockRequest->method('getMethod')->willReturn('POST');

        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->method('__toString')->willReturn("/db/neo4j/query/v2/tx/{$transactionId}/commit");

        $mockRequest->method('getUri')->willReturn($mockUri);

        $mockStream = Utils::streamFor('');
        $mockRequest->method('getBody')->willReturn($mockStream);

        $this->psr17Factory->method('createRequest')
            ->willReturn($mockRequest);

        $factory = new Neo4jRequestFactory($this->baseUri);
        $request = $factory->buildCommitRequest($database, $transactionId);

        $this->assertEquals('POST', $request['method']);
        $this->assertEquals("{$this->baseUri}/db/{$database}/query/v2/tx/{$transactionId}/commit", (string) $request['uri']);
    }
}
