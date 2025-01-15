<?php

namespace Neo4j\QueryAPI\Objects;

class ProfiledQueryPlanArguments
{
    public function __construct(
        private readonly ?int    $globalMemory = null,
        private readonly ?string $plannerImpl = null,
        private readonly ?int    $memory = null,
        private readonly ?string $stringRepresentation = null,
        private readonly ?string $runtime = null,
        private readonly ?int    $time = null,
        private readonly ?int    $pageCacheMisses = null,
        private readonly ?int    $pageCacheHits = null,
        private readonly ?string $runtimeImpl = null,
        private readonly ?int    $version = null,
        private readonly ?int    $dbHits = null,
        private readonly ?int    $batchSize = null,
        private readonly ?string $details = null,
        private readonly ?string $plannerVersion = null,
        private readonly ?string $pipelineInfo = null,
        private readonly ?string $runtimeVersion = null,
        private readonly ?int    $id = null,
        private readonly ?float  $estimatedRows = null,
        private readonly ?string $planner = null,
        private readonly ?int    $rows = null
    )
    {
    }

    public function getGlobalMemory(): int
    {
        return $this->globalMemory;
    }

    public function getPlannerImpl(): string
    {
        return $this->plannerImpl;
    }

    public function getMemory(): int
    {
        return $this->memory;
    }

    public function getStringRepresentation(): string
    {
        return $this->stringRepresentation;
    }

    public function getRuntime(): string
    {
        return $this->runtime;
    }

    public function getTime(): int
    {
        return $this->time;
    }
    public function getPageCacheMisses(): int
    {
        return $this->pageCacheMisses;
    }

     private function getPageCacheHits():int
     {
         return $this->pageCacheHits;
     }
    public function getRuntimeImpl(): string
    {
        return $this->runtimeImpl;
    }
    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDbHits(): int
    {
        return $this->dbHits;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function getPlannerVersion(): string
    {
        return $this->plannerVersion;
    }

    public function getPipelineInfo(): string
    {
        return $this->pipelineInfo;
    }

    public function getRuntimeVersion(): string
    {
        return $this->runtimeVersion;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEstimatedRows(): float
    {
        return $this->estimatedRows;
    }

    public function getPlanner(): string
    {
        return $this->planner;
    }

    public function getRows(): int
    {
        return $this->rows;
    }
}
