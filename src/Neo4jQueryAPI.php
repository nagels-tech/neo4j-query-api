<?php

namespace Neo4j\QueryAPI;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlanArguments;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\ResultSet;
use Neo4j\QueryAPI\Results\ResultRow;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use stdClass;

class Neo4jQueryAPI
{
    private ClientInterface $client;
    private AuthenticateInterface $auth;

    public function __construct(ClientInterface $client, AuthenticateInterface $auth)
    {
        $this->client = $client;
        $this->auth = $auth;
    }

    /**
     * @throws \Exception
     */
    public static function login(string $address, AuthenticateInterface $auth = null): self
    {
        $client = new Client([
            'base_uri' => rtrim($address, '/'),
            'timeout' => 10.0,
            'headers' => [
                'Content-Type' => 'application/vnd.neo4j.query',
                'Accept' => 'application/vnd.neo4j.query',
            ],
        ]);

        return new self($client, $auth ?? Authentication::basic());
    }

    /**
     * Executes a Cypher query on the Neo4j database.
     *
     * @throws Neo4jException
     * @throws RequestExceptionInterface
     */
    public function run(string $cypher, array $parameters = [], string $database = 'neo4j', Bookmarks $bookmark = null): ResultSet
    {
        try {
            // Prepare the payload
            $payload = [
                'statement' => $cypher,
                'parameters' => empty($parameters) ? new stdClass() : $parameters,
                'includeCounters' => true,
            ];

            // Include bookmarks if provided
            if ($bookmark !== null) {
                $payload['bookmarks'] = $bookmark->getBookmarks();
            }

            // Create the HTTP request
            $request = new Request('POST', '/db/' . $database . '/query/v2');
            $request = $this->auth->authenticate($request);
            $request = $request->withHeader('Content-Type', 'application/json');
            $request = $request->withBody(Utils::streamFor(json_encode($payload)));

            // Send the request and get the response
            $response = $this->client->sendRequest($request);
            $contents = $response->getBody()->getContents();

            // Parse the response data
            $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

            // Check for errors in the response from Neo4j
            if (isset($data['errors']) && count($data['errors']) > 0) {
                // If errors exist in the response, throw a Neo4jException
                $error = $data['errors'][0];
                throw new Neo4jException(
                    $error, // Pass the entire error array instead of just the message
                    0,
                    null,
                    $error
                );
            }

            // Parse the result set and return it
            return $this->parseResultSet($data);

        } catch (RequestExceptionInterface $e) {
            // Handle exceptions from the HTTP request
            $this->handleException($e);
        } catch (Neo4jException $e) {
            // Catch Neo4j specific exceptions (if thrown)
            throw $e; // Re-throw the exception
        }
    }

    private function parseResultSet(array $data): ResultSet
    {
        $ogm = new OGM();

        $keys = $data['data']['fields'] ?? [];
        $values = $data['data']['values'] ?? [];

        if (!is_array($values)) {
            throw new RuntimeException('Unexpected response format: values is not an array.');
        }

        $rows = array_map(function ($resultRow) use ($ogm, $keys) {
            $row = [];
            foreach ($keys as $index => $key) {
                $fieldData = $resultRow[$index] ?? null;
                $row[$key] = $ogm->map($fieldData);
            }
            return new ResultRow($row);
        }, $values);

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

        $profile = isset($data['profiledQueryPlan']) ? $this->createProfileData($data['profiledQueryPlan']) : null;

        return new ResultSet(
            $rows,
            $resultCounters,
            new Bookmarks($data['bookmarks'] ?? []),
            $profile
        );
    }

    private function handleException(RequestExceptionInterface $e): void
    {
        $response = $e->getResponse();
        if ($response !== null) {
            $contents = $response->getBody()->getContents();
            $errorResponse = json_decode($contents, true);
            throw Neo4jException::fromNeo4jResponse($errorResponse, $e);
        }
        throw $e;
    }

    public function beginTransaction(): array
    {
        $request = new Request('POST', '/db/neo4j/tx'); // Adjust endpoint as needed

        // Apply authentication, if provided
        if ($this->auth instanceof AuthenticateInterface) {
            $request = $this->auth->authenticate($request);
        }

        try {
            $response = $this->client->send($request);
            $responseBody = json_decode($response->getBody()->getContents(), true);

            // Validate the response for transaction ID
            if (isset($responseBody['commit'])) {
                return $responseBody; // Successful transaction
            }

            throw new RuntimeException('Missing transaction ID in the response.');
        } catch (Exception $e) {
            throw new RuntimeException("Failed to begin transaction: {$e->getMessage()}", 0, $e);
        }
    }



    private function createProfileData(array $data): ProfiledQueryPlan
    {
        $ogm = new OGM();

        $mappedArguments = array_map(function ($value) use ($ogm) {
            if (is_array($value) && array_key_exists('$type', $value) && array_key_exists('_value', $value)) {
                return $ogm->map($value);
            }
            return $value;
        }, $data['arguments'] ?? []);

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
            rows: $mappedArguments['Rows'] ?? null
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
            $queryArguments,
            children: [],
            identifiers: $data['identifiers'] ?? []
        );

        foreach ($data['children'] ?? [] as $child) {
            $profiledQueryPlan->addChild($this->createProfileData($child));
        }

        return $profiledQueryPlan;
    }
}
