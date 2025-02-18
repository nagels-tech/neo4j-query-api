<?php

namespace Neo4j\QueryAPI;

use GuzzleHttp\Client;
use Neo4j\QueryAPI\Authentication\AuthenticateInterface;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\Configuration;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class Neo4jQueryAPI
{
    private Configuration $config;

    public function __construct(
        private ClientInterface $client,
        private ResponseParser $responseParser,
        private Neo4jRequestFactory $requestFactory,
        ?Configuration $config = null
    ) {
        $this->config = $config ?? new Configuration(baseUri: 'http://myaddress'); // Default configuration if not provided
    }

    /**
     * @api
     */
    public static function login(string $address = null, ?AuthenticateInterface $auth = null, ?Configuration $config = null): self
    {
        $client = new Client();
        $config = $config ?? new Configuration(baseUri: $address);


        /* if the user now passes a config object without an address it will break.

$config = new Configuration(database: 'mydb');

QueryApi::login('http://myaddress', Authentication::fromEnvironment(), $config);*/
        return new self(
            client: $client,
            responseParser: new ResponseParser(new OGM()),
            requestFactory: new Neo4jRequestFactory(
                psr17Factory: new Psr17Factory(),
                streamFactory: new Psr17Factory(),
                configuration: $config,
                auth: $auth ?? Authentication::fromEnvironment()
            ),
            config: $config
        );
    }

    public function getConfig(): Configuration
    {
        return $this->config;
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
            $errorResponse = json_decode((string) $response->getBody(), true);
            throw Neo4jException::fromNeo4jResponse($errorResponse, $e);
        }

        throw new Neo4jException(['message' => $e->getMessage()], 500, $e);
    }
}
