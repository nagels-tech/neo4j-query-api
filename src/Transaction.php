<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\ResultSet;
use Neo4j\QueryAPI\Results\ResultRow;
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
     * @api
     * @param string $query The Cypher query to be executed.
     * @param array $parameters Parameters for the query.
     * @return ResultSet The result rows in ResultSet format.
     * @throws Neo4jException If the response structure is invalid.
     */
    public function run(string $query, array $parameters): ResultSet
    {
        $response = $this->client->post("/db/neo4j/query/v2/tx/{$this->transactionId}", [
            'headers' => [
                'Authorization' => Authentication::basic('neo4j', '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0')->getheader(),
                'neo4j-cluster-affinity' => $this->clusterAffinity,
            ],
            'json' => [
                'statement' => $query,
                'parameters' => empty($parameters) ? new stdClass() : $parameters,
                'includeCounters' => true
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
            return new ResultSet(
                rows: [],
                counters: new ResultCounters(
                    containsUpdates: $data['counters']['containsUpdates'],
                    nodesCreated: $data['counters']['nodesCreated'],
                    nodesDeleted: $data['counters']['nodesDeleted'],
                    propertiesSet: $data['counters']['propertiesSet'],
                    relationshipsCreated: $data['counters']['relationshipsCreated'],
                    relationshipsDeleted: $data['counters']['relationshipsDeleted'],
                    labelsAdded: $data['counters']['labelsAdded'],
                    labelsRemoved: $data['counters']['labelsRemoved'],
                    indexesAdded: $data['counters']['indexesAdded'],
                    indexesRemoved: $data['counters']['indexesRemoved'],
                    constraintsAdded: $data['counters']['constraintsAdded'],
                    constraintsRemoved: $data['counters']['constraintsRemoved'],
                    containsSystemUpdates: $data['counters']['containsSystemUpdates'],
                    systemUpdates: $data['counters']['systemUpdates']
                ),
                bookmarks: new Bookmarks($data['bookmarks'] ?? [])
            );
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

        return new ResultSet(
            rows: $rows,
            counters: new ResultCounters(
                containsUpdates: $data['counters']['containsUpdates'],
                nodesCreated: $data['counters']['nodesCreated'],
                nodesDeleted: $data['counters']['nodesDeleted'],
                propertiesSet: $data['counters']['propertiesSet'],
                relationshipsCreated: $data['counters']['relationshipsCreated'],
                relationshipsDeleted: $data['counters']['relationshipsDeleted'],
                labelsAdded: $data['counters']['labelsAdded'],
                labelsRemoved: $data['counters']['labelsRemoved'],
                indexesAdded: $data['counters']['indexesAdded'],
                indexesRemoved: $data['counters']['indexesRemoved'],
                constraintsAdded: $data['counters']['constraintsAdded'],
                constraintsRemoved: $data['counters']['constraintsRemoved'],
                containsSystemUpdates: $data['counters']['containsSystemUpdates'],
                systemUpdates: $data['counters']['systemUpdates']
            ),
            bookmarks: new Bookmarks($data['bookmarks'] ?? [])
        );
    }

    /**
     * @api
     */
    public function commit(): void
    {
        $this->client->post("/db/neo4j/query/v2/tx/{$this->transactionId}/commit", [
            'headers' => [
                'Authorization' => Authentication::basic('neo4j', '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0')->getheader(),
                'neo4j-cluster-affinity' => $this->clusterAffinity,
            ],
        ]);
    }

    /**
     * @api
     */
    public function rollback(): void
    {
        $this->client->delete("/db/neo4j/query/v2/tx/{$this->transactionId}", [
            'headers' => [
                'neo4j-cluster-affinity' => $this->clusterAffinity,
            ],
        ]);
    }
}
