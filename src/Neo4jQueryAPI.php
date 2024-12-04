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

    public function run(string $cypher, string $database = 'neo4j'): array
    {

        $payload = [
            'statement' => $cypher,
            'parameters' => new stdClass(),
        ];

        $response = $this->client->post('/db/' . $database . '/query/v2', [
            'json' => $payload,
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        return $data;


    }
}
