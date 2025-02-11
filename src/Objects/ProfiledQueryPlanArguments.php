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
        //        private readonly ?int    $pageCacheHits = null,
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
    ) {
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getGlobalMemory(): ?int
    {
        return $this->globalMemory;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getPlannerImpl(): ?string
    {
        return $this->plannerImpl;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getMemory(): ?int
    {
        return $this->memory;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getStringRepresentation(): ?string
    {
        return $this->stringRepresentation;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getRuntime(): ?string
    {
        return $this->runtime;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getTime(): ?int
    {
        return $this->time;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */

    public function getPageCacheMisses(): ?int
    {
        return $this->pageCacheMisses;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    /*private function getPageCacheHits(): ?int
    {
        return $this->pageCacheHits;
    }*/

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getRuntimeImpl(): ?string
    {
        return $this->runtimeImpl;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getVersion(): ?int
    {
        return $this->version;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */

    public function getDbHits(): ?int
    {
        return $this->dbHits;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getBatchSize(): ?int
    {
        return $this->batchSize;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getDetails(): ?string
    {
        return $this->details;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getPlannerVersion(): ?string
    {
        return $this->plannerVersion;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getPipelineInfo(): ?string
    {
        return $this->pipelineInfo;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getRuntimeVersion(): ?string
    {
        return $this->runtimeVersion;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getEstimatedRows(): ?float
    {
        return $this->estimatedRows;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getPlanner(): ?string
    {
        return $this->planner;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getRows(): ?int
    {
        return $this->rows;
    }
}
