<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Configuration;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Neo4j\QueryAPI\Neo4jRequestFactory;
use Neo4j\QueryAPI\Objects\Authentication;
use RuntimeException;

final class Neo4jRequestFactoryTest extends TestCase
{
    private RequestFactoryInterface&MockObject $psr17Factory;

    private StreamFactoryInterface&MockObject $streamFactory;

    private string $address = '';
    private string $authHeader = '';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->psr17Factory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);

        $address = getenv('NEO4J_ADDRESS');
        $this->address = is_string($address) ? $address : '';

        $auth = Authentication::fromEnvironment();
        $this->authHeader = $auth->getHeader();
    }

    public function testBuildRunQueryRequest(): void
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
            new Configuration($this->address),
            Authentication::fromEnvironment(),
        );
        $request = $factory->buildRunQueryRequest($cypher, $parameters);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals($uri, (string) $request->getUri());
        $payload = json_encode([]);
        if ($payload === false) {
            throw new RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }

    }

    public function testBuildBeginTransactionRequest(): void
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
            new Configuration($this->address),
            Authentication::fromEnvironment(),
        );
        $request = $factory->buildBeginTransactionRequest();

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals($uri, (string) $request->getUri());
    }

    public function testBuildCommitRequest(): void
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
            new Configuration($this->address),
            Authentication::fromEnvironment(),
        );
        $request = $factory->buildCommitRequest($database, $transactionId);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals($uri, (string) $request->getUri());
    }

    public function testBuildRollbackRequest(): void
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
            new Configuration($this->address),
            Authentication::fromEnvironment(),
        );
        $request = $factory->buildRollbackRequest($database, $transactionId);

        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals($uri, (string) $request->getUri());
    }

    public function testCreateRequestWithHeadersAndBody(): void
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
            new Configuration($this->address),
            Authentication::fromEnvironment(),
        );

        $request = $factory->buildRunQueryRequest($cypher, $parameters);

        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertEquals('application/vnd.neo4j.query', $request->getHeaderLine('Accept'));
        $this->assertEquals($this->authHeader, $request->getHeaderLine('Authorization'));
        $payload = json_encode([]);
        if ($payload === false) {
            throw new RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }

    }

    public function testCreateRequestWithoutAuthorizationHeader(): void
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
            new Configuration($this->address),
            Authentication::noAuth(),
        );

        $request = $factory->buildRunQueryRequest($cypher, $parameters);
        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertEquals('application/vnd.neo4j.query', $request->getHeaderLine('Accept'));
        $this->assertEmpty($request->getHeaderLine('Authorization'));
        $payload = json_encode([]);
        if ($payload === false) {
            throw new RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }
    }
}
