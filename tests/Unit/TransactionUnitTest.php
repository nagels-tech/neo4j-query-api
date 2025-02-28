<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Transaction;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\ResponseParser;
use Neo4j\QueryAPI\Neo4jRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

final class TransactionUnitTest extends TestCase
{
    private Transaction $transaction;
    private $clientMock;
    private $responseParserMock;
    private $requestFactoryMock;
    private $requestMock;
    private $responseMock;
    private string $transactionId = 'tx123';
    private string $clusterAffinity = 'LEADER';

    #[\Override]
    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->responseParserMock = $this->createMock(ResponseParser::class);
        $this->requestFactoryMock = $this->createMock(Neo4jRequestFactory::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->transaction = new Transaction(
            $this->clientMock,
            $this->responseParserMock,
            $this->requestFactoryMock,
            $this->clusterAffinity,
            $this->transactionId
        );
    }

    public function testRunExecutesQuerySuccessfully(): void
    {
        $query = 'MATCH (n) RETURN n';
        $parameters = [];
        $resultSetMock = $this->createMock(ResultSet::class);

        $this->requestFactoryMock->expects($this->once())
            ->method('buildTransactionRunRequest')
            ->with($query, $parameters, $this->transactionId, $this->clusterAffinity)
            ->willReturn($this->requestMock);

        $this->clientMock->expects($this->once())
            ->method('sendRequest')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        $this->responseParserMock->expects($this->once())
            ->method('parseRunQueryResponse')
            ->with($this->responseMock)
            ->willReturn($resultSetMock);

        $result = $this->transaction->run($query, $parameters);
        $this->assertInstanceOf(ResultSet::class, $result);
    }


    public function testHandleRequestExceptionWithoutResponse(): void
    {
        $exceptionMock = $this->createMock(RequestExceptionInterface::class);

        $reflection = new \ReflectionClass($exceptionMock);
        $property = $reflection->getParentClass()->getProperty('message');
        $property->setValue($exceptionMock, 'Request failed');

        $this->expectException(Neo4jException::class);
        $this->expectExceptionMessage('Request failed');

        $reflection = new \ReflectionClass($this->transaction);
        $method = $reflection->getMethod('handleRequestException');

        $method->invoke($this->transaction, $exceptionMock);
    }



    public function testCommitSendsCommitRequest(): void
    {
        $this->requestFactoryMock->expects($this->once())
            ->method('buildCommitRequest')
            ->with($this->transactionId, $this->clusterAffinity)
            ->willReturn($this->requestMock);

        $this->clientMock->expects($this->once())
            ->method('sendRequest')
            ->with($this->requestMock);

        $this->transaction->commit();
    }

    public function testRollbackSendsRollbackRequest(): void
    {
        $this->requestFactoryMock->expects($this->once())
            ->method('buildRollbackRequest')
            ->with($this->transactionId, $this->clusterAffinity)
            ->willReturn($this->requestMock);

        $this->clientMock->expects($this->once())
            ->method('sendRequest')
            ->with($this->requestMock);

        $this->transaction->rollback();
    }
}
