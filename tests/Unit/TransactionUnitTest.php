<?php

namespace Neo4j\QueryAPI\Tests\Unit;

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
    private ClientInterface $client;
    private Neo4jRequestFactory $requestFactory;
    private ResponseParser $responseParser;
    private Transaction $transaction;

    private string $transactionId = 'txn123';
    private string $clusterAffinity = 'leader';

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

    public function testRunCallsBuildTransactionRunRequest()
    {
        $query = "CREATE (:Person {name: \$name})";
        $parameters = ['name' => 'Alice'];

        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResultSet = $this->createMock(\Neo4j\QueryAPI\Results\ResultSet::class);

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

    public function testCommitCallsBuildCommitRequest()
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

    public function testRollbackCallsBuildRollbackRequest()
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
