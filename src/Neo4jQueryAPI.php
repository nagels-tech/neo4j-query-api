<?php

namespace Neo4j\QueryAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Neo4j\QueryAPI\Enums\AccessMode;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Neo4j\QueryAPI\loginConfig;

class Neo4jQueryAPI
{
    private Client $client;
    private loginConfig $loginConfig;
    private Configuration $config;
    private ResponseParser $responseParser;

    public function __construct(LoginConfig $loginConfig, ResponseParser $responseParser, Configuration $config)
    {
        $this->loginConfig = $loginConfig;
        $this->responseParser = $responseParser;
        $this->config = $config;

        $this->client = new Client([
            'base_uri' => rtrim($this->loginConfig->baseUrl, '/'),
            'timeout'  => 10.0,
            'headers'  => [
                'Authorization' => 'Basic ' . $this->loginConfig->authToken,
                'Accept'  => 'application/vnd.neo4j.query',
            ],
        ]);
    }


    /**
     * Static method to create an instance with login details.
     */
    public static function login(): self
    {
        $loginConfig = loginConfig::fromEnv();
        $config = new Configuration();

        return new self($loginConfig, new ResponseParser(new OGM()), $config);
    }



    /**
     * Executes a Cypher query.
     *
     * @throws Neo4jException|RequestExceptionInterface
     */
    public function run(string $cypher, array $parameters = []): ResultSet
    {
        try {
            $payload = [
                'statement'      => $cypher,
                'parameters'     => empty($parameters) ? new \stdClass() : $parameters,
                'includeCounters' => $this->config->includeCounters,
                'accessMode'     => $this->config->accessMode->value,
            ];

            if (!empty($this->config->bookmark)) {
                $payload['bookmarks'] = $this->config->bookmark;
            }



            //            if ($impersonatedUser !== null) {
            //                $payload['impersonatedUser'] = $impersonatedUser;
            //            }
            error_log('Neo4j Payload: ' . json_encode($payload));

            $response = $this->client->post("/db/{$this->config->database}/query/v2", ['json' => $payload]);

            return $this->responseParser->parseRunQueryResponse($response);
        } catch (RequestException $e) {
            error_log('Neo4j Request Failed: ' . $e->getMessage());

            $this->handleRequestException($e);
        }
    }

    /**
     * Starts a transaction.
     */
    public function beginTransaction(string $database = 'neo4j'): Transaction
    {
        $response = $this->client->post("/db/{$database}/query/v2/tx");

        $clusterAffinity = $response->getHeaderLine('neo4j-cluster-affinity');
        $responseData = json_decode($response->getBody(), true);
        $transactionId = $responseData['transaction']['id'];

        return new Transaction($this->client, $clusterAffinity, $transactionId);
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
