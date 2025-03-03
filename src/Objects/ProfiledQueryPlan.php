<?php

namespace Neo4j\QueryAPI\Objects;

final class ProfiledQueryPlan
{
    public function __construct(
        public readonly ProfiledQueryPlanArguments $arguments,
        public readonly int $dbHits = 0,
        public readonly int $records = 0,
        public readonly bool $hasPageCacheStats = false,
        public readonly int $pageCacheHits = 0,
        public readonly int $pageCacheMisses = 0,
        public readonly float $pageCacheHitRatio = 0.0,
        public readonly int $time = 0,
        public readonly string $operatorType = '',
        public readonly array $children = [],
        public readonly array $identifiers = []
    ) {
    }
}
