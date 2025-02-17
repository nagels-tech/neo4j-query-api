<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Enums\AccessMode;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlanArguments;
use Psr\Http\Message\ResponseInterface;
use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Results\ResultRow;
use RuntimeException;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;

class ResponseParser
{
    public function __construct(private readonly OGM $ogm)
    {
    }

    public function parseRunQueryResponse(ResponseInterface $response): ResultSet
    {
        $data = $this->validateAndDecodeResponse($response);

        $rows = $this->mapRows($data['data']['fields'] ?? [], $data['data']['values'] ?? []);
        $counters = isset($data['counters']) ? $this->buildCounters($data['counters']) : null;
        $bookmarks = $this->buildBookmarks($data['bookmarks'] ?? []);
        $profiledQueryPlan = $this->buildProfiledQueryPlan($data['profiledQueryPlan'] ?? null);
        $accessMode = $this->getAccessMode($data['accessMode'] ?? '');

        return new ResultSet($rows, $counters, $bookmarks, $profiledQueryPlan, $accessMode);
    }

    private function validateAndDecodeResponse(ResponseInterface $response): array
    {
        if ($response->getStatusCode() >= 400) {
            $errorResponse = json_decode((string)$response->getBody(), true);
            throw Neo4jException::fromNeo4jResponse($errorResponse);
        }

        $contents = $response->getBody()->getContents();
        $data = json_decode($contents, true);

        if (!isset($data['data'])) {
            throw new RuntimeException('Invalid response: "data" key missing or null.');
        }

        return $data;
    }

    /**
     * @return list<ResultRow>
     */
    private function mapRows(array $fields, array $values): array
    {
        $rows = array_map(fn($row) => new ResultRow($row), $values);
        return array_values($rows);
    }


    private function buildCounters(array $countersData): ResultCounters
    {
        return new ResultCounters(
            containsUpdates: $countersData['containsUpdates'] ?? false,
            nodesCreated: $countersData['nodesCreated'] ?? 0,
            nodesDeleted: $countersData['nodesDeleted'] ?? 0,
            propertiesSet: $countersData['propertiesSet'] ?? 0,
            relationshipsCreated: $countersData['relationshipsCreated'] ?? 0,
            relationshipsDeleted: $countersData['relationshipsDeleted'] ?? 0,
            labelsAdded: $countersData['labelsAdded'] ?? 0,
            labelsRemoved: $countersData['labelsRemoved'] ?? 0,
            indexesAdded: $countersData['indexesAdded'] ?? 0,
            indexesRemoved: $countersData['indexesRemoved'] ?? 0,
            constraintsAdded: $countersData['constraintsAdded'] ?? 0,
            constraintsRemoved: $countersData['constraintsRemoved'] ?? 0,
            systemUpdates: $countersData['systemUpdates'] ?? 0,
        );
    }

    private function buildBookmarks(array $bookmarksData): Bookmarks
    {
        return new Bookmarks($bookmarksData);
    }

    private function getAccessMode(string $accessModeData): AccessMode
    {
        return AccessMode::tryFrom($accessModeData) ?? AccessMode::WRITE;
    }

    private function buildProfiledQueryPlan(?array $queryPlanData): ?ProfiledQueryPlan
    {

        if ($queryPlanData === null || empty($queryPlanData)) {
            return null;
        }

        /**
         * @var array<string, mixed> $mappedArguments
         */
        $mappedArguments = array_map(function ($value): mixed {
            if (is_array($value) && array_key_exists('$type', $value) && array_key_exists('_value', $value)) {
                return $this->ogm->map($value);
            }
            return $value;
        }, $queryPlanData['arguments'] ?? []);

        $queryArguments = new ProfiledQueryPlanArguments(
            globalMemory: $mappedArguments['GlobalMemory'] ?? null,
            plannerImpl: $mappedArguments['planner-impl'] ?? null, //('planner-impl', $mappedArguments) ? $mappedArguments['planner-impl'] : null,
            memory: array_key_exists('Memory', $mappedArguments) ? $mappedArguments['Memory'] : null,
            stringRepresentation: array_key_exists('string-representation', $mappedArguments) ? $mappedArguments['string-representation'] : null,
            runtime: array_key_exists('runtime', $mappedArguments) ? $mappedArguments['runtime'] : null,
            time: array_key_exists('Time', $mappedArguments) ? $mappedArguments['Time'] : null,
            pageCacheMisses: array_key_exists('PageCacheMisses', $mappedArguments) ? $mappedArguments['PageCacheMisses'] : null,
            pageCacheHits: array_key_exists('PageCacheHits', $mappedArguments) ? $mappedArguments['PageCacheHits'] : null,
            runtimeImpl: array_key_exists('runtime-impl', $mappedArguments) ? $mappedArguments['runtime-impl'] : null,
            version: array_key_exists('version', $mappedArguments) ? $mappedArguments['version'] : null,
            dbHits: array_key_exists('DbHits', $mappedArguments) ? $mappedArguments['DbHits'] : null,
            batchSize: array_key_exists('batch-size', $mappedArguments) ? $mappedArguments['batch-size'] : null,
            details: array_key_exists('Details', $mappedArguments) ? $mappedArguments['Details'] : null,
            plannerVersion: array_key_exists('planner-version', $mappedArguments) ? $mappedArguments['planner-version'] : null,
            pipelineInfo: array_key_exists('PipelineInfo', $mappedArguments) ? $mappedArguments['PipelineInfo'] : null,
            runtimeVersion: array_key_exists('runtime-version', $mappedArguments) ? $mappedArguments['runtime-version'] : null,
            id: array_key_exists('Id', $mappedArguments) ? $mappedArguments['Id'] : null,
            estimatedRows: array_key_exists('EstimatedRows', $mappedArguments) ? $mappedArguments['EstimatedRows'] : null,
            planner: array_key_exists('planner', $mappedArguments) ? $mappedArguments['planner'] : null,
            rows: array_key_exists('Rows', $mappedArguments) ? $mappedArguments['Rows'] : null
        );
        $children = array_map(fn ($child) => $this->buildProfiledQueryPlan($child), $queryPlanData['children'] ?? []);

        return new ProfiledQueryPlan(
            $queryPlanData['dbHits'] ?? 0,
            $queryPlanData['records'] ?? 0,
            $queryPlanData['hasPageCacheStats'] ?? false,
            $queryPlanData['pageCacheHits'] ?? 0,
            $queryPlanData['pageCacheMisses'] ?? 0,
            $queryPlanData['pageCacheHitRatio'] ?? 0.0,
            $queryPlanData['time'] ?? 0,
            $queryPlanData['operatorType'] ?? '',
            $queryArguments,
            $children,
            $queryPlanData['identifiers'] ?? []
        );
    }
}
