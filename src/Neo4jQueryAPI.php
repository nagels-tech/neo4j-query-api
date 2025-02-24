<?php

namespace Neo4j\QueryAPI;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use http\Exception\RuntimeException;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Psr\Http\Client\ClientInterface;
use Neo4j\QueryAPI\Authentication\AuthenticateInterface;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Results\ResultSet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\RequestExceptionInterface;

final class Neo4jQueryAPI
{
    public function __construct(
        private ClientInterface     $client,
        private ResponseParser      $responseParser,
        private Neo4jRequestFactory $requestFactory
    ) {
    }

    /**
     * @api
     */
    public static function login(string $address, AuthenticateInterface $auth = null): self
    {
        $client = Psr18ClientDiscovery::find();

        return new self(
            client: $client,
            responseParser: new ResponseParser(
                ogm: new OGM()
            ),
            requestFactory: new Neo4jRequestFactory(
                psr17Factory: Psr17FactoryDiscovery::findRequestFactory(),
                streamFactory: Psr17FactoryDiscovery::findStreamFactory(),
                configuration: new Configuration(
                    baseUri: $address
                ),
                auth: $auth ?? Authentication::fromEnvironment()
            )
        );
    }

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

    public function beginTransaction(): Transaction
    {
        $request = $this->requestFactory->buildBeginTransactionRequest();
        $response = $this->client->sendRequest($request);

        try {
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            $this->handleRequestException($e);
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
     *
     * @return never
     */
    private function handleRequestException(RequestExceptionInterface $e): void
    {
        // ✅ Corrected: Check if exception has a response
        $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;

        if ($response instanceof ResponseInterface) {
            $errorResponse = json_decode((string)$response->getBody(), true);
            throw Neo4jException::fromNeo4jResponse($errorResponse, $e);
        }

        throw new Neo4jException(['message' => $e->getMessage()], 500, $e);
    }
}
