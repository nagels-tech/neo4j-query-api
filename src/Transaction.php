<?php

namespace Neo4j\QueryAPI;

use GuzzleHttp\Client;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use Psr\Http\Client\ClientInterface;
use stdClass;

class Transaction
{
    public function __construct(
        private ClientInterface $client,
        private string $clusterAffinity,
        private string $transactionId
    ) {
    }

    /**
     * Execute a Cypher query within the transaction.
     *
     * @param string $query The Cypher query to be executed.
     * @param array $parameters Parameters for the query.
     * @return ResultSet The result rows in ResultSet format.
     * @throws Neo4jException If the response structure is invalid.
     */
    public function run(string $query, array $parameters): ResultSet
    {
        $response = $this->client->post("/db/neo4j/query/v2/tx/{$this->transactionId}", [
            'headers' => [
                'neo4j-cluster-affinity' => $this->clusterAffinity,
            ],
            'json' => [
                'statement' => $query,
                'parameters' => empty($parameters) ? new stdClass() : $parameters,
            ],
        ]);

        $responseBody = $response->getBody()->getContents();
        $data = json_decode($responseBody, true);

        if (!isset($data['data']['fields'], $data['data']['values'])) {
            throw new Neo4jException([
                'message' => 'Unexpected response structure from Neo4j',
                'response' => $data,
            ]);
        }

        $keys = $data['data']['fields'];
        $values = $data['data']['values'];

        if (empty($values)) {
            return new ResultSet([]);
        }

        $ogm = new OGM();
        $rows = array_map(function ($resultRow) use ($ogm, $keys) {
            $data = [];
            foreach ($keys as $index => $key) {
                $fieldData = $resultRow[$index] ?? null;
                $data[$key] = $ogm->map($fieldData);
            }
            return new ResultRow($data);
        }, $values);

        return new ResultSet($rows);
    }

    public function commit(): void
    {
        $this->client->post("/db/neo4j/query/v2/tx/{$this->transactionId}/commit", [
            'headers' => [
                'neo4j-cluster-affinity' => $this->clusterAffinity,
            ],
        ]);
    }

    public function rollback(): void
    {
        $this->client->delete("/db/neo4j/query/v2/tx/{$this->transactionId}", [
            'headers' => [
                'neo4j-cluster-affinity' => $this->clusterAffinity,
            ],
        ]);
    }
}
