<?php

namespace Neo4j\QueryAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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
                'Content-Type' => 'application/json',
            ],
        ]);

        return new self($client);
    }

    public function run(string $cypher, array $parameters, string $database = 'neo4j'): array
    {
        $payload = [
            'statement' => $cypher,
            'parameters' => $parameters  === [] ? new  stdClass() : $parameters,
        ];

        $response = $this->client->post('/db/' . $database . '/query/v2', [
            'json' => $payload,
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        return $this->normalizeResults($data);
    }

    private function normalizeResults(array $results): array
    {
        if (isset($results['data']['fields']) && isset($results['data']['values'])) {
            $fields = $results['data']['fields'];
            $values = $results['data']['values'];

            $normalizedData = array_map(function ($row) use ($fields) {
                return ['row' => array_combine($fields, $row)];
            }, $values);

            return ['data' => $normalizedData];
        }

        return $results; // Return unchanged if no transformation is needed
    }
}
