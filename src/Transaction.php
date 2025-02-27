<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Results\ResultSet;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 *  @api
 */
class Transaction
{
    public function __construct(
        private ClientInterface $client,
        private ResponseParser $responseParser,
        private Neo4jRequestFactory $requestFactory,
        private string $clusterAffinity,
        private string $transactionId
    ) {
    }

    /**
     * Execute a Cypher query within the transaction.
     * @api
     * @param string $query The Cypher query to be executed.
     * @param array $parameters Parameters for the query.
     * @return ResultSet The result rows in ResultSet format.
     * @throws Neo4jException If the response structure is invalid.
     */
    public function run(string $query, array $parameters): ResultSet
    {
        $request = $this->requestFactory->buildTransactionRunRequest($query, $parameters, $this->transactionId, $this->clusterAffinity);

        $response = null;

        try {
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            $this->handleRequestException($e);
        }

        if (!$response instanceof ResponseInterface) {
            throw new Neo4jException(['message' => 'Failed to receive a valid response from Neo4j'], 500);
        }

        return $this->responseParser->parseRunQueryResponse($response);
    }

    /**
     * @api
     */
    public function commit(): void
    {
        $request = $this->requestFactory->buildCommitRequest($this->transactionId, $this->clusterAffinity);
        $this->client->sendRequest($request);
    }

    /**
     * @api
     */
    public function rollback(): void
    {
        $request = $this->requestFactory->buildRollbackRequest($this->transactionId, $this->clusterAffinity);
        $this->client->sendRequest($request);
    }

    /**
     * Handles request exceptions by parsing error details and throwing a Neo4jException.
     *
     * @throws Neo4jException
     */
    private function handleRequestException(RequestExceptionInterface $e): void
    {
        $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;

        if ($response instanceof ResponseInterface) {
            $errorResponse = json_decode((string)$response->getBody(), true);
            throw Neo4jException::fromNeo4jResponse($errorResponse, $e);
        }

        throw new Neo4jException(['message' => $e->getMessage()], 500, $e);
    }
}
