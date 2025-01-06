<?php

namespace Neo4j\QueryAPI;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Neo4j\QueryAPI\Objects\QueryArguments;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Psr\Http\Client\RequestExceptionInterface;
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
                'Accept' => 'application/vnd.neo4j.query',
            ],
        ]);

        return new self($client);
    }

    /**
     * @throws Neo4jException
     * @throws RequestExceptionInterface
     */
    public function run(string $cypher, array $parameters = [], string $database = 'neo4j'): ResultSet
    {
        try {
            // Prepare the payload for the request
            $payload = [
                'statement' => $cypher,
                'parameters' => empty($parameters) ? new stdClass() : $parameters,
                'includeCounters' => true
            ];

            // Execute the request to the Neo4j server
            $response = $this->client->post('/db/' . $database . '/query/v2', [
                'json' => $payload,
            ]);

            // Decode the response body
            $data = json_decode($response->getBody()->getContents(), true);
            $ogm = new OGM();

            // Extract result rows
            $keys = $data['data']['fields'];
            $values = $data['data']['values'];
            $rows = array_map(function ($resultRow) use ($ogm, $keys) {
                $data = [];
                foreach ($keys as $index => $key) {
                    $fieldData = $resultRow[$index] ?? null;
                    $data[$key] = $ogm->map($fieldData);
                }
                return new ResultRow($data);
            }, $values);

            // Extract profile data, if available
            $profiledQueryPlan = null;
            if (isset($data['profiledQueryPlan'])) {
                $profiledQueryPlan = new ProfiledQueryPlan(
                    $data['profile']['dbHits'],
                    $data['profile']['records'],
                    $data['profile']['hasPageCacheStats'],
                    $data['profile']['pageCacheHits'],
                    $data['profile']['pageCacheMisses'],
                    $data['profile']['pageCacheHitRatio'],
                    $data['profile']['time'],
                    $data['profile']['operatorType'],
                    $data['profile']['arguments']
                );
            }
            $queryArguments = null;
            if (isset($data['profiledQueryPlan']['arguments'])) {
                $queryArguments = new QueryArguments(
                    $data['profile']['globalMemory'] ?? 0,
                    $data['profile']['plannerImpl'] ?? '',
                    $data['profile']['memory'] ?? 0,
                    $data['profile']['stringRepresentation'] ?? '',
                    $data['profile']['runtime'] ?? '',
                    $data['profile']['runtimeImpl'] ?? '',
                    $data['profile']['dbHits'] ?? 0,
                    $data['profile']['batchSize'] ?? 0,
                    $data['profile']['details'] ?? '',
                    $data['profile']['plannerVersion'] ?? '',
                    $data['profile']['pipelineInfo'] ?? '',
                    $data['profile']['runtimeVersion'] ?? '',
                    $data['profile']['id'] ?? 0,
                    $data['profile']['estimatedRows'] ?? 0.0,
                    $data['profile']['planner'] ?? '',
                    $data['profile']['rows'] ?? 0
                );
            }

            // Return a ResultSet containing rows, counters, and the profiled query plan
            return new ResultSet(
                $rows,
                new ResultCounters(
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
                $profiledQueryPlan // Pass the profiled query plan here
            );
        } catch (RequestExceptionInterface $e) {
            $response = $e->getResponse();
            if ($response !== null) {
                $contents = $response->getBody()->getContents();
                $errorResponse = json_decode($contents, true);

                throw Neo4jException::fromNeo4jResponse($errorResponse, $e);
            }

            throw $e;
        }
    }

    public function beginTransaction(string $database = 'neo4j'): Transaction
    {
        $response = $this->client->post("/db/neo4j/query/v2/tx");

        $clusterAffinity = $response->getHeaderLine('neo4j-cluster-affinity');
        $responseData = json_decode($response->getBody(), true);
        $transactionId = $responseData['transaction']['id'];

        return new Transaction($this->client, $clusterAffinity, $transactionId);
    }
}
