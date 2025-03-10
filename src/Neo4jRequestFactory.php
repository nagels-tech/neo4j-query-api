<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Authentication\AuthenticateInterface;
use Neo4j\QueryAPI\Enums\AccessMode;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Neo4jRequestFactory
{
    public function __construct(
        private RequestFactoryInterface $psr17Factory,
        private StreamFactoryInterface  $streamFactory,
        private Configuration           $configuration,
        private AuthenticateInterface   $auth
    ) {
    }

    public function buildRunQueryRequest(
        string $cypher,
        array  $parameters = []
    ): RequestInterface {
        return $this->createRequest("/db/{$this->configuration->database}/query/v2", $cypher, $parameters);
    }

    public function buildBeginTransactionRequest(): RequestInterface
    {
        return $this->createRequest("/db/{$this->configuration->database}/query/v2/tx", null, null);
    }

    public function buildCommitRequest(string $transactionId, string $clusterAffinity): RequestInterface
    {
        return $this->createRequest("/db/{$this->configuration->database}/query/v2/tx/{$transactionId}/commit", null, null)
            ->withHeader("neo4j-cluster-affinity", $clusterAffinity);
    }

    public function buildRollbackRequest(string $transactionId, string $clusterAffinity): RequestInterface
    {
        return $this->createRequest("/db/{$this->configuration->database}/query/v2/tx/{$transactionId}/rollback", null, null)
            ->withHeader("neo4j-cluster-affinity", $clusterAffinity)
            ->withMethod("DELETE");
    }

    public function buildTransactionRunRequest(string $query, array $parameters, string $transactionId, string $clusterAffinity): RequestInterface
    {
        return $this->createRequest("/db/neo4j/query/v2/tx/{$transactionId}", $query, $parameters)
            ->withHeader("neo4j-cluster-affinity", $clusterAffinity);
    }

    private function createRequest(string $uri, ?string $cypher, ?array $parameters): RequestInterface
    {
        $request = $this->psr17Factory->createRequest('POST', $this->configuration->baseUri . $uri);

        $payload = [];

        if ($this->configuration->includeCounters) {
            $payload['includeCounters'] = true;
        }

        if ($this->configuration->accessMode === AccessMode::READ) {
            $payload['accessMode'] = AccessMode::READ;
        }

        if ($cypher !== null && $cypher !== '') {
            $payload['statement'] = $cypher;
        }

        if ($parameters !== null && $parameters !== []) {
            $payload['parameters'] = $parameters;
        }

        /** @psalm-suppress RedundantCondition */
        if ($this->configuration->bookmarks !== null) {
            $payload['bookmarks'] = $this->configuration->bookmarks;
        }

        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Accept', 'application/vnd.neo4j.query');

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $stream = $this->streamFactory->createStream($body);

        $request = $request->withBody($stream);

        return $this->auth->authenticate($request);
    }
}
