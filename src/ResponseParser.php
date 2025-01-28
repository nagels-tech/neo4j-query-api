<?php

namespace Neo4j\QueryAPI;

use Psr\Http\Message\ResponseInterface;
use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Results\ResultRow;
use RuntimeException;

class ResponseParser
{
    public function __construct(private OGM $ogm) {}

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
        $counters = $this->buildCounters($data['counters'] ?? []);
        $bookmarks = $this->buildBookmarks($data['bookmarks'] ?? []);

        return new ResultSet($rows, $counters, $bookmarks);
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
        $contents = (string) $response->getBody();
        $data = json_decode($contents, true);

        if (!isset($data['data'])) {
            throw new RuntimeException('Invalid response: "data" key missing.');
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
                $mapped[$key] = $this->ogm->map($row[$index] ?? null);
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
            containsUpdates: $countersData['containsUpdates'] ?? false
        // Add more counters as needed.
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
}
