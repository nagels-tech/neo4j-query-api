<?php

namespace Neo4j\QueryAPI;

use GuzzleHttp\Client;
use Neo4j\QueryAPI\Results\ResultSet;

class Transaction
{
    private Client $client;
    private string $baseUrl;
    private array $headers;
    private ?string $transactionId = null;

    public function __construct(string $baseUrl, string $username, string $password)
    {
        $this->client = new Client();
        $this->baseUrl = $baseUrl;
        $auth = base64_encode("$username:$password");
        $this->headers = [
            'Authorization' => 'Basic ' . $auth,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function begin(): void
    {
        $response = $this->client->post($this->baseUrl .'/db/neo4j'. '/tx', [
            'headers' => $this->headers,
            'body' => json_encode([]),
        ]);

        $responseData = json_decode($response->getBody(), true);
        $this->transactionId = $responseData['commit'] ?? null;
    }

    public function run(string $statement, array $params = []): ResultSet
    {
        if (!$this->transactionId) {
            $this->begin();
        }

        $payload = json_encode([
            'statements' => [
                [
                    'statement' => $statement,
                    'parameters' => $params,
                ],
            ],
        ]);

        $response = $this->client->post($this->baseUrl  .'/db/neo4j'.'/tx/' . $this->transactionId, [
            'headers' => $this->headers,
            'body' => $payload,
        ]);

        $responseData = json_decode($response->getBody(), true);

        return new ResultSet($responseData['results'] ?? []);
    }

    public function commit(): ?ResultSet
    {
        if (!$this->transactionId) {
            throw new \Exception("No active transaction to commit.");
        }

        $response = $this->client->post($this->transactionId, [
            'headers' => $this->headers,
            'body' => json_encode([]),
        ]);

        $responseData = json_decode($response->getBody(), true);
        $this->transactionId = null;

        return new ResultSet($responseData['results'] ?? []);
    }

    public function rollback(): void
    {
        if ($this->transactionId) {
            $this->client->delete($this->baseUrl . '/tx/' . $this->transactionId, [
                'headers' => $this->headers,
            ]);
            $this->transactionId = null;
        }
    }
}
