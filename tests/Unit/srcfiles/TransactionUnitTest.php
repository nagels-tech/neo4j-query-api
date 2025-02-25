<?php

namespace Neo4j\QueryAPI\Tests\Unit\srcfiles;

use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Transaction;
use Neo4j\QueryAPI\Neo4jRequestFactory;
use Neo4j\QueryAPI\ResponseParser;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @api
 */
class TransactionUnitTest extends TestCase
{
    private MockObject $client;
    private MockObject $requestFactory;
    private MockObject $responseParser;
    private Transaction $transaction;

    private string $transactionId = 'txn123';
    private string $clusterAffinity = 'leader';

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(Neo4jRequestFactory::class);
        $this->responseParser = $this->createMock(ResponseParser::class);

        $this->transaction = new Transaction(
            $this->client,
            $this->responseParser,
            $this->requestFactory,
            $this->clusterAffinity,
            $this->transactionId
        );
    }

    public function testRunCallsBuildTransactionRunRequest(): void
    {
        $query = "CREATE (:Person {name: \$name})";
        $parameters = ['name' => 'Alice'];

        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResultSet = $this->createMock(ResultSet::class);

        $this->requestFactory->expects($this->once())
            ->method('buildTransactionRunRequest')
            ->with($query, $parameters, $this->transactionId, $this->clusterAffinity)
            ->willReturn($mockRequest);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $this->responseParser->expects($this->once())
            ->method('parseRunQueryResponse')
            ->with($mockResponse)
            ->willReturn($mockResultSet);

        $result = $this->transaction->run($query, $parameters);

        $this->assertSame($mockResultSet, $result);
    }

    public function testCommitCallsBuildCommitRequest(): void
    {
        $mockRequest = $this->createMock(RequestInterface::class);

        $this->requestFactory->expects($this->once())
            ->method('buildCommitRequest')
            ->with($this->transactionId, $this->clusterAffinity)
            ->willReturn($mockRequest);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest);

        $this->transaction->commit();
    }

    public function testRollbackCallsBuildRollbackRequest(): void
    {
        $mockRequest = $this->createMock(RequestInterface::class);

        $this->requestFactory->expects($this->once())
            ->method('buildRollbackRequest')
            ->with($this->transactionId, $this->clusterAffinity)
            ->willReturn($mockRequest);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest);

        $this->transaction->rollback();
    }
}
