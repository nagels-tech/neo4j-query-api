<?php

namespace Neo4j\QueryAPI;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Psr\Http\Client\RequestExceptionInterface;
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
     * @throws Neo4jException
     * @throws RequestExceptionInterface
     */
    public function run(string $cypher, array $parameters, string $database = 'neo4j'): ResultSet
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
            $data = json_decode($response->getBody()->getContents(), true);
            $ogm = new OGM();

            $keys = $data['data']['fields'];
            $values = $data['data']['values'];
            $rows = array_map(function ($resultRow) use ($ogm, $keys) {
                $data = [];
                foreach ($keys as $index => $key) {
                    $fieldData = $resultRow[$index] ?? null;
                    $data[$key] = $ogm->map($fieldData);
                }
                return new ResultRow($data);
            }, $values);

            return new ResultSet($rows);
        } catch (RequestExceptionInterface $e) {
            $response = $e->getResponse();
            if ($response !== null) {
                $contents = $response->getBody()->getContents();
                $errorResponse = json_decode($contents, true);

                throw Neo4jException::fromNeo4jResponse($errorResponse, $e);
            }

            throw $e;
        }
    }

}
