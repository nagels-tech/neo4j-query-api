<?php

namespace Neo4j\QueryAPI\Objects;

class ProfiledQueryPlan
{
    public readonly int $dbHits;
    public readonly int $records;
    public readonly bool $hasPageCacheStats;
    public readonly int $pageCacheHits;
    public readonly int $pageCacheMisses;
    public readonly float $pageCacheHitRatio;
    public readonly int $time;
    public readonly string $operatorType;
    public readonly ProfiledQueryPlanArguments $arguments;
    public readonly array $children;
    public readonly array $identifiers;

    public function __construct(
        int $dbHits = 0,
        int $records = 0,
        bool $hasPageCacheStats = false,
        int $pageCacheHits = 0,
        int $pageCacheMisses = 0,
        float $pageCacheHitRatio = 0.0,
        int $time = 0,
        string $operatorType = '',
        ProfiledQueryPlanArguments $arguments,
        array $children = [],
        array $identifiers = []
    ) {
        $this->dbHits = $dbHits;
        $this->records = $records;
        $this->hasPageCacheStats = $hasPageCacheStats;
        $this->pageCacheHits = $pageCacheHits;
        $this->pageCacheMisses = $pageCacheMisses;
        $this->pageCacheHitRatio = $pageCacheHitRatio;
        $this->time = $time;
        $this->operatorType = $operatorType;
        $this->arguments = $arguments;
        $this->children = $children;
        $this->identifiers = $identifiers;
    }
}
