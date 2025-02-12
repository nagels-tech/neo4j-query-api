<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Enums\AccessMode;
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
    public function __construct(private OGM $ogm)
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
        $contents = (string) $response->getBody()->getContents();
        $data = json_decode($contents, true);

        if (!isset($data['data']) || $data['data'] === null) {
            throw new RuntimeException('Invalid response: "data" key missing or null.');
        }

        return $data;
    }

    private function mapRows(array $keys, array $values): array
    {
        return array_map(function ($row) use ($keys) {
            $mapped = [];
            foreach ($keys as $index => $key) {
                $fieldData = $row[$index] ?? null;
                if (is_string($fieldData)) {
                    $fieldData = ['$type' => 'String', '_value' => $fieldData];
                }
                $mapped[$key] = $this->ogm->map($fieldData);
            }
            return new ResultRow($mapped);
        }, $values);
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
        if (!$queryPlanData) {
            return null;
        }

        $queryArguments = new ProfiledQueryPlanArguments();
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
