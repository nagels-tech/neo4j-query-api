<?php

namespace Neo4j\QueryAPI;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Neo4j\QueryAPI\Exception\Neo4jException;
use RuntimeException;
use stdClass;

class Neo4jQueryAPI
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public static function login(string $address, string $username, string $password): self
    {

        $client = new Client([
            'base_uri' => rtrim($address, '/'),
            'timeout' => 10.0,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("$username:$password"),
                'Content-Type' => 'application/vnd.neo4j.query',
                'Accept'=>'application/vnd.neo4j.query',
            ],
        ]);

        return new self($client);
    }

    /**
     * @throws GuzzleException
     */
    public function run(string $cypher, array $parameters, string $database = 'neo4j'): array
    {
        try {
            // Prepare the payload for the request
            $payload = [
                'statement' => $cypher,
                'parameters' => empty($parameters) ? new stdClass() : $parameters,
            ];

            // Execute the request to the Neo4j server
            $response = $this->client->post('/db/' . $database . '/query/v2', [
                'json' => $payload,
            ]);

            // Decode the response body
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            // Catch any HTTP request errors
            $errorResponse = [
                'code' => 'Neo.HttpRequestError',
                'message' => 'HTTP request failed: ' . $e->getMessage(),
            ];
            throw Neo4jException::fromNeo4jResponse($errorResponse);
        } catch (Exception $e) {
            // Catch any other unexpected errors
            $errorResponse = [
                'code' => 'Neo.UnknownError',
                'message' => 'An unknown error occurred: ' . $e->getMessage(),
            ];
            throw Neo4jException::fromNeo4jResponse($errorResponse);
        }
    }

}
