<?php

namespace Neo4j\QueryAPI;

use GuzzleHttp\Client;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Results\ResultRow;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use stdClass;

class Transaction
{

    public function __construct(private ClientInterface $client, private string $clusterAffinity, private string $transactionId)
    {
    }

    /**
     * Create a node in Neo4j with a specified label and properties.
     *
     * @param string $query The Cypher query to be executed.
     * @param $parameters
     * @return array The response data from Neo4j.
     */
    public function run(string $query, array $parameters): array
    {
            // Execute the request to the Neo4j server
            $response = $this->client->post("/db/neo4j/query/v2/tx", [
                'headers' => [
                    'neo4j-cluster-affinity' => $this->clusterAffinity,
                ],
                'json' => [

                            'statement' => $query,
                            'parameters' => empty($parameters) ? new stdClass() : $parameters, // Pass the parameters array here

                ],
            ]);

            // Decode the response body
            $data = json_decode($response->getBody()->getContents(), true);

            // Initialize the OGM (Object Graph Mapping) class
            $ogm = new OGM();

            // Extract keys (field names) and values (actual data)
            $keys = $data['results'][0]['columns'];
            $values = $data['results'][0]['data'];

            // Process each row of the result and map them using OGM
            $rows = array_map(function ($resultRow) use ($ogm, $keys) {
                $data = [];
                foreach ($keys as $index => $key) {
                    $fieldData = $resultRow['row'][$index] ?? null;
                    $data[$key] = $ogm->map($fieldData); // Map the field data to the appropriate object format
                }
                return new ResultRow($data); // Wrap the mapped data in a ResultRow object
            }, $values);

            return $rows; // Return the processed rows as an array of ResultRow objects


    }



    public function commit(): void
    {
        $this->client->post("/db/neo4j/query/v2/tx/{$this->transactionId}/commit", [
            'headers' => [
                'neo4j-cluster-affinity' => $this->clusterAffinity,
            ]
        ]);
    }

    public function rollback(): void
    {
        $this->client->delete("/db/neo4j/query/v2/tx/{$this->transactionId}", [
            'headers' => [
                'neo4j-cluster-affinity' => $this->clusterAffinity,
            ]
        ]);
    }
}
