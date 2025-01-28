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

class Neo4jQueryAPI
{
    private Client $client;
    private Configuration $config;
    private ResponseParser $responseParser;

    public function __construct(Configuration $config, ResponseParser $responseParser)
    {
        $this->config = $config;
        $this->responseParser = $responseParser;

        $this->client = new Client([
            'base_uri' => rtrim($this->config->getBaseUrl(), '/'),
            'timeout' => 10.0,
            'headers' => $this->config->getDefaultHeaders(),
        ]);
    }

    /**
     * Static method to create an instance with login details.
     */
    public static function login(string $address, string $username, string $password): self
    {
        $authToken = base64_encode("$username:$password");
        $config = (new Configuration())
            ->setBaseUrl($address)
            ->setAuthToken($authToken);

        return new self($config, new ResponseParser(new OGM()));
    }

    /**
     * Executes a Cypher query.
     *
     * @throws Neo4jException|RequestExceptionInterface
     */
    public function run(string $cypher, array $parameters = [], string $database = 'neo4j', Bookmarks $bookmark = null, ?string $impersonatedUser = null, AccessMode $accessMode = AccessMode::WRITE): ResultSet
    {
        try {
            $payload = [
                'statement' => $cypher,
                'parameters' => empty($parameters) ? new \stdClass() : $parameters,
                'includeCounters' => true,
                'accessMode' => $accessMode->value,
            ];

            if ($bookmark !== null) {
                $payload['bookmarks'] = $bookmark->getBookmarks();
            }

            if ($impersonatedUser !== null) {
                $payload['impersonatedUser'] = $impersonatedUser;
            }

            $response = $this->client->post("/db/{$database}/query/v2", ['json' => $payload]);

            return $this->responseParser->parseRunQueryResponse($response);
        } catch (RequestException $e) {
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
