<?php

namespace Neo4j\QueryAPI;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Neo4j\QueryAPI\Objects\ChildQueryPlan;
use Neo4j\QueryAPI\Objects\QueryArguments;
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

            $response = $this->client->post('/db/' . $database . '/query/v2', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $ogm = new OGM();

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
            $mappedArguments[$key] = $ogm->map($value);
        }

        $queryArguments = new QueryArguments(
            $mappedArguments['globalMemory'],
            $mappedArguments['plannerImpl'],
            $mappedArguments['memory'],
            $mappedArguments['stringRepresentation'],
            is_string($mappedArguments['runtime'] ? $mappedArguments['runtime'] : json_encode($mappedArguments['runtime'])),
            $mappedArguments['time'],
            $mappedArguments['runtimeImpl'],
            $mappedArguments['dbHits'],
            $mappedArguments['batchSize'],
            $mappedArguments['details'],
            $mappedArguments['plannerVersion'],
            $mappedArguments['pipelineInfo'],
            $mappedArguments['runtimeVersion'],
            $mappedArguments['id'],
            (float)($mappedArguments['estimatedRows'] ?? 0.0),
            is_string($mappedArguments['planner'] ? $mappedArguments['planner'] : json_encode($mappedArguments['planner'])),
            $mappedArguments['rows']
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
