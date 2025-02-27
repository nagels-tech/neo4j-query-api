<?php

namespace Neo4j\QueryAPI\Objects;

class ProfiledQueryPlanArguments
{
    public function __construct(
        public readonly ?int    $globalMemory = null,
        public readonly ?string $plannerImpl = null,
        public readonly ?int    $memory = null,
        public readonly ?string $stringRepresentation = null,
        public readonly ?string $runtime = null,
        public readonly ?int    $time = null,
        public readonly ?int    $pageCacheMisses = null,
        public readonly ?int    $pageCacheHits = null,
        public readonly ?string $runtimeImpl = null,
        public readonly ?int    $version = null,
        public readonly ?int    $dbHits = null,
        public readonly ?int    $batchSize = null,
        public readonly ?string $details = null,
        public readonly ?string $plannerVersion = null,
        public readonly ?string $pipelineInfo = null,
        public readonly null|string|float $runtimeVersion = null,
        public readonly ?int    $id = null,
        public readonly ?float  $estimatedRows = null,
        public readonly ?string $planner = null,
        public readonly ?int    $rows = null
    ) {
    }
}
