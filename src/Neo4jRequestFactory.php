<?php

namespace Neo4j\QueryAPI;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 *  @api
 */
class Neo4jRequestFactory
{
    private string $baseUri;
    private ?string $authHeader = null;
    private RequestFactoryInterface $psr17Factory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        RequestFactoryInterface $psr17Factory,
        StreamFactoryInterface $streamFactory,
        string $baseUri,
        ?string $authHeader = null
    ) {
        $this->psr17Factory = $psr17Factory;
        $this->streamFactory = $streamFactory;
        $this->baseUri = $baseUri;
        $this->authHeader = $authHeader;
    }

    public function buildRunQueryRequest(
        string $database,
        string $cypher,
        array $parameters = [],
        bool $includeCounters = true,
        ?array $bookmarks = null
    ): RequestInterface {
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

    public function buildBeginTransactionRequest(string $database): RequestInterface
    {
        $uri = rtrim($this->baseUri, '/') . "/db/{$database}/query/v2/tx";
        return $this->createRequest('POST', $uri);
    }

    public function buildCommitRequest(string $database, string $transactionId): RequestInterface
    {
        $uri = rtrim($this->baseUri, '/') . "/db/{$database}/query/v2/tx/{$transactionId}/commit";
        return $this->createRequest('POST', $uri);
    }

    public function buildRollbackRequest(string $database, string $transactionId): RequestInterface
    {
        $uri = rtrim($this->baseUri, '/') . "/db/{$database}/query/v2/tx/{$transactionId}/rollback";
        return $this->createRequest('POST', $uri);
    }

    private function createRequest(string $method, string $uri, ?string $body = null): RequestInterface
    {
        $request = $this->psr17Factory->createRequest($method, $uri);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($this->authHeader) {
            $headers['Authorization'] = $this->authHeader;
        }

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $stream = $this->streamFactory->createStream($body);
            $request = $request->withBody($stream);
        }

        return $request;
    }
}
