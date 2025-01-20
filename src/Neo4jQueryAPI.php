<?php

namespace Neo4j\QueryAPI;

use Exception;
use GuzzleHttp\Client;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlanArguments;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Psr\Http\Client\RequestExceptionInterface;
use RuntimeException;
use stdClass;
use Neo4j\QueryAPI\Objects\Bookmarks;

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
    public function run(string $cypher, array $parameters = [], string $database = 'neo4j', Bookmarks $bookmark = null): ResultSet
    {
        try {
            $payload = [
                'statement' => $cypher,
                'parameters' => empty($parameters) ? new stdClass() : $parameters,
                'includeCounters' => true,
            ];

            if ($bookmark !== null) {
                $payload['bookmarks'] = $bookmark->getBookmarks();
            }

            $response = $this->client->request('POST', '/db/' . $database . '/query/v2', [
                'json' => $payload,
            ]);

            $contents = $response->getBody()->getContents();
            $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
            $ogm = new OGM();

            $keys = $data['data']['fields'] ?? [];
            $values = $data['data']['values'] ?? []; // Ensure $values is an array

            if (!is_array($values)) {
                throw new RuntimeException('Unexpected response format: values is not an array.');
            }

            $rows = array_map(function ($resultRow) use ($ogm, $keys) {
                $data = [];
                foreach ($keys as $index => $key) {
                    $fieldData = $resultRow[$index] ?? null;
                    $data[$key] = $ogm->map($fieldData);
                }
                return new ResultRow($data);
            }, $values);
            $profile = isset($data['profiledQueryPlan']) ? $this->createProfileData($data['profiledQueryPlan']) : null;

            $resultCounters = new ResultCounters(
                containsUpdates: $data['counters']['containsUpdates'] ?? false,
                nodesCreated: $data['counters']['nodesCreated'] ?? 0,
                nodesDeleted: $data['counters']['nodesDeleted'] ?? 0,
                propertiesSet: $data['counters']['propertiesSet'] ?? 0,
                relationshipsCreated: $data['counters']['relationshipsCreated'] ?? 0,
                relationshipsDeleted: $data['counters']['relationshipsDeleted'] ?? 0,
                labelsAdded: $data['counters']['labelsAdded'] ?? 0,
                labelsRemoved: $data['counters']['labelsRemoved'] ?? 0,
                indexesAdded: $data['counters']['indexesAdded'] ?? 0,
                indexesRemoved: $data['counters']['indexesRemoved'] ?? 0,
                constraintsAdded: $data['counters']['constraintsAdded'] ?? 0,
                constraintsRemoved: $data['counters']['constraintsRemoved'] ?? 0,
                containsSystemUpdates: $data['counters']['containsSystemUpdates'] ?? false,
                systemUpdates: $data['counters']['systemUpdates'] ?? 0
            );

            return new ResultSet(
                $rows,
                $resultCounters,
                new Bookmarks($data['bookmarks'] ?? []),
                $profile
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

    private function createProfileData(array $data): ProfiledQueryPlan
    {
        $ogm = new OGM();

        // Map arguments using OGM
        $arguments = $data['arguments'];
        $mappedArguments = [];
        foreach ($arguments as $key => $value) {
            if (is_array($value) && array_key_exists('$type', $value) && array_key_exists('_value', $value)) {
                $mappedArguments[$key] = $ogm->map($value);
            } else {
                $mappedArguments[$key] = $value;
            }
        }

        $queryArguments = new ProfiledQueryPlanArguments(
            globalMemory: $mappedArguments['GlobalMemory'] ?? null,
            plannerImpl: $mappedArguments['planner-impl'] ?? null,
            memory: $mappedArguments['Memory'] ?? null,
            stringRepresentation: $mappedArguments['string-representation'] ?? null,
            runtime: $mappedArguments['runtime'] ?? null,
            time: $mappedArguments['Time'] ?? null,
            pageCacheMisses: $mappedArguments['PageCacheMisses'] ?? null,
            pageCacheHits: $mappedArguments['PageCacheHits'] ?? null,
            runtimeImpl: $mappedArguments['runtime-impl'] ?? null,
            version: $mappedArguments['version'] ?? null,
            dbHits: $mappedArguments['DbHits'] ?? null,
            batchSize: $mappedArguments['batch-size'] ?? null,
            details: $mappedArguments['Details'] ?? null,
            plannerVersion: $mappedArguments['planner-version'] ?? null,
            pipelineInfo: $mappedArguments['PipelineInfo'] ?? null,
            runtimeVersion: $mappedArguments['runtime-version'] ?? null,
            id: $mappedArguments['Id'] ?? null,
            estimatedRows: $mappedArguments['EstimatedRows'] ?? null,
            planner: $mappedArguments['planner'] ?? null,
            rows: $mappedArguments['Rows' ?? null]
        );


        $profiledQueryPlan = new ProfiledQueryPlan(
            $data['dbHits'],
            $data['records'],
            $data['hasPageCacheStats'],
            $data['pageCacheHits'],
            $data['pageCacheMisses'],
            $data['pageCacheHitRatio'],
            $data['time'],
            $data['operatorType'],
            $queryArguments
        );

        // Process children recursively
        foreach ($data['children'] as $child) {
            $childQueryPlan = $this->createProfileData($child);
            $profiledQueryPlan->addChild($childQueryPlan);
        }

        return $profiledQueryPlan;
    }

}
