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
use Neo4j\QueryAPI\Objects\Point;

final class ResponseParser
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
    /**
     * @param list<string> $fields
     * @param list<array<array-key, mixed>> $values
     * @return list<ResultRow>
     */
    private function mapRows(array $fields, array $values): array
    {
        return array_map(
            fn (array $row): ResultRow => new ResultRow(
                array_combine(
                    $fields,
                    array_map([$this, 'formatOGMOutput'], $row)
                ) ?: [] // Ensure array_combine never returns false
            ),
            $values
        );
    }

    /**
     * Ensures mapped output follows expected format
     *
     * @param mixed $value
     * @return mixed
     */
    private function formatOGMOutput(mixed $value): mixed
    {
        if (is_array($value) && array_key_exists('$type', $value) && array_key_exists('_value', $value)) {
            return $this->ogm->map($value);
        }

        return $value;
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
        $mappedArguments = array_map(function (mixed $value): mixed {
            if (is_array($value) && isset($value['$type']) && isset($value['_value'])) {
                return $this->ogm->map($value);
            }

            return $value;
        }, $queryPlanData['arguments'] ?? []);


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

        $children = array_map(
            fn (array $child): ?ProfiledQueryPlan => $this->buildProfiledQueryPlan($child),
            $queryPlanData['children'] ?? []
        );

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
