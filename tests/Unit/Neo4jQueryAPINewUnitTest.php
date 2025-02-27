<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Configuration;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Neo4jRequestFactory;
use Neo4j\QueryAPI\ResponseParser;
use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Neo4jQueryAPINewUnitTest extends TestCase
{
    private Neo4jQueryAPI $api;
    private ClientInterface $clientMock;
    private ResponseParser $responseParserMock;
    private Neo4jRequestFactory $requestFactoryMock;
    private Configuration $configMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->responseParserMock = $this->createMock(ResponseParser::class);
        $this->requestFactoryMock = $this->createMock(Neo4jRequestFactory::class);
        $this->configMock = $this->createMock(Configuration::class);

        $this->api = new Neo4jQueryAPI(
            client: $this->clientMock,
            responseParser: $this->responseParserMock,
            requestFactory: $this->requestFactoryMock,
            config: $this->configMock
        );
    }

    public function testLoginCreatesInstance(): void
    {
        $apiInstance = Neo4jQueryAPI::login('http://localhost:7474');
        $this->assertInstanceOf(Neo4jQueryAPI::class, $apiInstance);
    }

    public function testGetConfigReturnsCorrectConfig(): void
    {
        $this->assertSame($this->configMock, $this->api->getConfig());
    }

    public function testRunExecutesQueryAndReturnsResultSet(): void
    {
        $cypher = "MATCH (n) RETURN n";
        $parameters = [];

        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResultSet = $this->createMock(ResultSet::class);

        $this->requestFactoryMock
        ->method('buildRunQueryRequest')
        ->willReturn($mockRequest);

        $this->clientMock
            ->method('sendRequest')
            ->willReturn($mockResponse);
        $this->responseParserMock
            ->method('parseRunQueryResponse')
            ->willReturn($mockResultSet);

        $result = $this->api->run($cypher, $parameters);

        $this->assertSame($mockResultSet, $result);
    }

    public function testHandleRequestExceptionThrowsNeo4jException(): void
    {
        $this->expectException(Neo4jException::class);

        $mockException = $this->createMock(RequestExceptionInterface::class);
        $this->invokeMethod($this->api, 'handleRequestException', [$mockException]);
    }

    private function invokeMethod($object, string $methodName, array $parameters = []):array
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }
}
