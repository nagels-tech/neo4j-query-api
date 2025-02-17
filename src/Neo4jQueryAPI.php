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
use RuntimeException;

class Neo4jQueryAPI
{
    public function __construct(

        private ClientInterface     $client,
        private ResponseParser      $responseParser,
        private Neo4jRequestFactory $requestFactory,


    )
    {

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

        $response = null;

        try {
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            $this->handleRequestException($e);
        }
        if ($response === null) {
            throw new \RuntimeException('Failed to get a response');
        }

        return $this->responseParser->parseRunQueryResponse($response);
    }


    /**
     * Starts a transaction.
     */
    public function beginTransaction(): Transaction
    {
        $request = $this->requestFactory->buildBeginTransactionRequest();

        $response = null;

        try {
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            $this->handleRequestException($e);
        }

        if ($response === null) {
            throw new \RuntimeException('No response received for transaction request');
        }

        $clusterAffinity = $response->getHeaderLine('neo4j-cluster-affinity');
        $body = $response->getBody()->getContents();

        $responseData = json_decode($body, true);
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
    public function handleRequestException(RequestExceptionInterface $e): void
    {
        throw new \RuntimeException('Request failed: ' . $e->getMessage(), 0, $e);
    }
}

