<?php

namespace Neo4j\QueryAPI;

use GuzzleHttp\Client;
use Neo4j\QueryAPI\Authentication\AuthenticateInterface;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Results\ResultSet;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class Neo4jQueryAPI
{
    public function __construct(
        private ClientInterface $client,
        private ResponseParser $responseParser,
        private Neo4jRequestFactory $requestFactory
    ) {
    }

    /**
     * @api
     */
    public static function login(string $address, AuthenticateInterface $auth = null): self
    {
        $client = new Client();

        return new self(
            client: $client,
            responseParser: new ResponseParser(
                ogm: new OGM()
            ),
            requestFactory: new Neo4jRequestFactory(
                psr17Factory: new Psr17Factory(),
                streamFactory: new Psr17Factory(),
                configuration: new Configuration(
                    baseUri: $address
                ),
                auth: $auth ?? Authentication::fromEnvironment()
            )
        );
    }



    /**
     * Executes a Cypher query.
     */
    public function run(string $cypher, array $parameters = []): ResultSet
    {
        $request = $this->requestFactory->buildRunQueryRequest($cypher, $parameters);

        try {
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            $this->handleRequestException($e);
        }

        return $this->responseParser->parseRunQueryResponse($response);
    }

    /**
     * Starts a transaction.
     */
    public function beginTransaction(): Transaction
    {
        $request = $this->requestFactory->buildBeginTransactionRequest();

        try {
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            $this->handleRequestException($e);
        }

        $clusterAffinity = $response->getHeaderLine('neo4j-cluster-affinity');
        $responseData = json_decode($response->getBody(), true);
        $transactionId = $responseData['transaction']['id'];

        return new Transaction(
            $this->client,
            $this->responseParser,
            $this->requestFactory,
            $clusterAffinity,
            $transactionId
        );
    }

    /**
     * Handles request exceptions by parsing error details and throwing a Neo4jException.
     *
     * @throws Neo4jException
     */
    private function handleRequestException(RequestExceptionInterface $e): void
    {
        $response = $e->getResponse();
        if ($response instanceof ResponseInterface) {
            $errorResponse = json_decode((string)$response->getBody(), true);
            throw Neo4jException::fromNeo4jResponse($errorResponse, $e);
        }

        throw new Neo4jException(['message' => $e->getMessage()], 500, $e);
    }
}
