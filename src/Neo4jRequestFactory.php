<?php

namespace Neo4j\QueryAPI;

use InvalidArgumentException;

class Neo4jRequestFactory
{
    private string $baseUri;
    private ?string $authHeader = null;

    public function __construct(string $baseUri, ?string $authHeader = null)
    {
        $this->baseUri = $baseUri;
        $this->authHeader = $authHeader;
    }

    /**
     * Builds a request for running a Cypher query.
     */
    public function buildRunQueryRequest(
        string $database,
        string $cypher,
        array  $parameters = [],
        bool   $includeCounters = true,
        ?array $bookmarks = null
    ): array {
        $payload = [
            'statement' => $cypher,
            'parameters' => empty($parameters) ? new \stdClass() : $parameters,
            'includeCounters' => $includeCounters,
        ];

        if ($bookmarks !== null) {
            $payload['bookmarks'] = $bookmarks;
        }

        $uri = rtrim($this->baseUri, '/') . "/db/{$database}/query/v2";

        return $this->createRequest('POST', $uri, json_encode($payload));
    }

    /**
     * Builds a request for starting a new transaction.
     */
    public function buildBeginTransactionRequest(string $database): array
    {
        $uri = rtrim($this->baseUri, '/') . "/db/{$database}/query/v2/tx";

        return $this->createRequest('POST', $uri);
    }

    /**
     * Builds a request for committing a transaction.
     */
    public function buildCommitRequest(string $database, string $transactionId): array
    {
        $uri = rtrim($this->baseUri, '/') . "/db/{$database}/query/v2/tx/{$transactionId}/commit";

        return $this->createRequest('POST', $uri);
    }

    /**
     * Builds a request for rolling back a transaction.
     */
    public function buildRollbackRequest(string $database, string $transactionId): array
    {
        $uri = rtrim($this->baseUri, '/') . "/db/{$database}/query/v2/tx/{$transactionId}/rollback";

        return $this->createRequest('POST', $uri);
    }

    /**
     * Helper method to create a request manually.
     */
    private function createRequest(string $method, string $uri, ?string $body = null): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($this->authHeader) {
            $headers['Authorization'] = $this->authHeader;
        }

        return [
            'method' => $method,
            'uri' => $uri,
            'headers' => $headers,
            'body' => $body,
        ];
    }
}
