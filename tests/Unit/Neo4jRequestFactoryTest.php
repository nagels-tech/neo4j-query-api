<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Neo4j\QueryAPI\Neo4jRequestFactory;
use Neo4j\QueryAPI\Objects\Authentication;

class Neo4jRequestFactoryTest extends TestCase
{
    private $psr17Factory;
    private $streamFactory;
    private string $address;
    private string $authHeader;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->psr17Factory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);

        $this->address = getenv('NEO4J_ADDRESS');

        $auth = Authentication::fromEnvironment();
        $this->authHeader = $auth->getHeader();
    }

    /**
     * Test for buildRunQueryRequest
     */
    public function testBuildRunQueryRequest()
    {
        $cypher = 'MATCH (n) RETURN n';
        $parameters = ['param1' => 'value1'];
        $database = 'neo4j';

        $payload = json_encode([
            'statement' => $cypher,
            'parameters' => $parameters,
            'includeCounters' => true,
        ]);
        $uri = "{$this->address}/db/{$database}/query/v2";

        $mockRequest = new Request('POST', $uri);
        $mockStream = Utils::streamFor($payload);

        $this->streamFactory->method('createStream')
            ->willReturn($mockStream);

        $this->psr17Factory->method('createRequest')
            ->willReturn($mockRequest);

        $factory = new Neo4jRequestFactory(
            $this->psr17Factory,
            $this->streamFactory,
            $this->address,
            $this->authHeader
        );
        $request = $factory->buildRunQueryRequest($database, $cypher, $parameters);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals($uri, (string) $request->getUri());
        $this->assertJsonStringEqualsJsonString($payload, (string) $request->getBody());
    }

    /**
     * Test for buildBeginTransactionRequest
     */
    public function testBuildBeginTransactionRequest()
    {
        $database = 'neo4j';
        $uri = "{$this->address}/db/{$database}/query/v2/tx";

        $mockRequest = new Request('POST', $uri);
        $mockStream = Utils::streamFor('');

        $this->streamFactory->method('createStream')
            ->willReturn($mockStream);

        $this->psr17Factory->method('createRequest')
            ->willReturn($mockRequest);

        $factory = new Neo4jRequestFactory(
            $this->psr17Factory,
            $this->streamFactory,
            $this->address
        );
        $request = $factory->buildBeginTransactionRequest($database);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals($uri, (string) $request->getUri());
    }

    /**
     * Test for buildCommitRequest
     */
    public function testBuildCommitRequest()
    {
        $database = 'neo4j';
        $transactionId = '12345';
        $uri = "{$this->address}/db/{$database}/query/v2/tx/{$transactionId}/commit";

        $mockRequest = new Request('POST', $uri);
        $mockStream = Utils::streamFor('');

        $this->streamFactory->method('createStream')
            ->willReturn($mockStream);

        $this->psr17Factory->method('createRequest')
            ->willReturn($mockRequest);

        $factory = new Neo4jRequestFactory(
            $this->psr17Factory,
            $this->streamFactory,
            $this->address
        );
        $request = $factory->buildCommitRequest($database, $transactionId);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals($uri, (string) $request->getUri());
    }

    /**
     * Test for buildRollbackRequest
     */
    public function testBuildRollbackRequest()
    {
        $database = 'neo4j';
        $transactionId = '12345';
        $uri = "{$this->address}/db/{$database}/query/v2/tx/{$transactionId}/rollback";

        $mockRequest = new Request('POST', $uri);
        $mockStream = Utils::streamFor('');

        $this->streamFactory->method('createStream')
            ->willReturn($mockStream);

        $this->psr17Factory->method('createRequest')
            ->willReturn($mockRequest);

        $factory = new Neo4jRequestFactory(
            $this->psr17Factory,
            $this->streamFactory,
            $this->address
        );
        $request = $factory->buildRollbackRequest($database, $transactionId);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals($uri, (string) $request->getUri());
    }

    /**
     * Test for createRequest method with headers and body
     */
    public function testCreateRequestWithHeadersAndBody()
    {
        $cypher = 'MATCH (n) RETURN n';
        $parameters = ['param1' => 'value1'];
        $database = 'neo4j';
        $uri = "{$this->address}/db/{$database}/query/v2";

        $payload = json_encode([
            'statement' => $cypher,
            'parameters' => $parameters,
            'includeCounters' => true,
        ]);

        $mockStream = Utils::streamFor($payload);
        $this->streamFactory->method('createStream')
            ->willReturn($mockStream);

        $mockRequest = new Request('POST', $uri);
        $this->psr17Factory->method('createRequest')
            ->willReturn($mockRequest);

        $factory = new Neo4jRequestFactory(
            $this->psr17Factory,
            $this->streamFactory,
            $this->address,
            $this->authHeader
        );

        $request = $factory->buildRunQueryRequest($database, $cypher, $parameters);

        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertEquals('application/json', $request->getHeaderLine('Accept'));
        $this->assertEquals($this->authHeader, $request->getHeaderLine('Authorization'));
        $this->assertJsonStringEqualsJsonString($payload, (string) $request->getBody());
    }

    /**
     * Test createRequest without Authorization header
     */
    public function testCreateRequestWithoutAuthorizationHeader()
    {
        $cypher = 'MATCH (n) RETURN n';
        $parameters = ['param1' => 'value1'];
        $database = 'neo4j';
        $uri = "{$this->address}/db/{$database}/query/v2";

        $payload = json_encode([
            'statement' => $cypher,
            'parameters' => $parameters,
            'includeCounters' => true,
        ]);

        $mockStream = Utils::streamFor($payload);
        $this->streamFactory->method('createStream')
            ->willReturn($mockStream);

        $mockRequest = new Request('POST', $uri);
        $this->psr17Factory->method('createRequest')
            ->willReturn($mockRequest);

        $factory = new Neo4jRequestFactory(
            $this->psr17Factory,
            $this->streamFactory,
            $this->address
        );

        $request = $factory->buildRunQueryRequest($database, $cypher, $parameters);

        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertEquals('application/json', $request->getHeaderLine('Accept'));
        $this->assertEmpty($request->getHeaderLine('Authorization'));
        $this->assertJsonStringEqualsJsonString($payload, (string) $request->getBody());
    }
}
