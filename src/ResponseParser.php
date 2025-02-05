<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Enums\AccessMode;
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

    /**
     * Parses the response from a run query operation.
     *
     * @param ResponseInterface $response
     * @return ResultSet
     * @throws RuntimeException
     */
    public function parseRunQueryResponse(ResponseInterface $response): ResultSet
    {
        $data = $this->validateAndDecodeResponse($response);

        $rows = $this->mapRows($data['data']['fields'] ?? [], $data['data']['values'] ?? []);
        $counters = null;
        if (array_key_exists('counters', $data)) {
            $counters = $this->buildCounters($data['counters']);
        }
        $bookmarks = $this->buildBookmarks($data['bookmarks'] ?? []);
        $profiledQueryPlan = $this->buildProfiledQueryPlan($data['profiledQueryPlan'] ?? null);
        $accessMode = $this->getAccessMode($data['accessMode'] ?? '');

        return new ResultSet($rows, $counters, $bookmarks, $profiledQueryPlan, $accessMode);
    }

    /**
     * Validates and decodes the API response.
     *
     * @param ResponseInterface $response
     * @return array
     * @throws RuntimeException
     */
    private function validateAndDecodeResponse(ResponseInterface $response): array
    {
        $contents = (string) $response->getBody()->getContents();
        $data = json_decode($contents, true);

        if (!isset($data['data']) || $data['data'] === null) {
            throw new RuntimeException('Invalid response: "data" key missing or null.');
        }

        return $data;
    }



    /**
     * Maps rows from the response data.
     *
     * @param array $keys
     * @param array $values
     * @return ResultRow[]
     */
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


    /**
     * Builds a ResultCounters object from the response data.
     *
     * @param array $countersData
     * @return ResultCounters
     */
    private function buildCounters(array $countersData): ResultCounters
    {
        return new ResultCounters(
            containsUpdates: $countersData['containsUpdates'] ?? false,
            systemUpdates:  $countersData['systemUpdates'] ?? false,
            nodesCreated: $countersData['nodesCreated'] ?? false,
            nodesDeleted: $countersData['nodesDeleted'] ?? false,
            propertiesSet: $countersData['propertiesSet'] ?? false,
            relationshipsDeleted: $countersData['relationshipsDeleted'] ?? false,
            relationshipsCreated: $countersData['relationshipsCreated'] ?? false,
            labelsAdded: $countersData['labelsAdded'] ?? false,
            labelsRemoved: $countersData['labelsRemoved'] ?? false,
            indexesAdded: $countersData['indexesAdded'] ?? false,
            indexesRemoved: $countersData['indexesRemoved'] ?? false,
            constraintsRemoved: $countersData['constraintsRemoved'] ?? false,
            constraintsAdded: $countersData['constraintsAdded'] ?? false,
        );
    }

    /**
     * Builds a Bookmarks object from the response data.
     *
     * @param array $bookmarksData
     * @return Bookmarks
     */
    private function buildBookmarks(array $bookmarksData): Bookmarks
    {
        return new Bookmarks($bookmarksData);
    }

    /**
     * Gets the access mode from response data.
     *
     * @param string $accessModeData
     * @return AccessMode
     */
    private function getAccessMode(string $accessModeData): AccessMode
    {
        return AccessMode::tryFrom($accessModeData) ?? AccessMode::WRITE;
    }
    /**
     * Builds a ProfiledQueryPlan object from the response data.
     *
     * @param array|null $queryPlanData
     * @return ?ProfiledQueryPlan
     */
    private function buildProfiledQueryPlan(?array $queryPlanData): ?ProfiledQueryPlan
    {
        if (!$queryPlanData) {
            return null;
        }

        return new ProfiledQueryPlan(
            $queryPlanData['dbHits'] ?? 0,
            $queryPlanData['records'] ?? 0,
            $queryPlanData['hasPageCacheStats'] ?? false,
            $queryPlanData['pageCacheHits'] ?? 0,
            $queryPlanData['pageCacheMisses'] ?? 0,
            $queryPlanData['pageCacheHitRatio'] ?? 0.0,
            $queryPlanData['time'] ?? 0,
            $queryPlanData['operatorType'] ?? '',
        );
    }
}
