<?php

namespace Neo4j\QueryAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
        $payload = [
            'statement' => $cypher,
            'parameters' => $parameters  === [] ? new  stdClass() : $parameters,
        ];

        $response = $this->client->post('/db/' . $database . '/query/v2', [
            'json' => $payload,
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }


}
