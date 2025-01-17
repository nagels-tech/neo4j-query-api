<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * @api
 */
class QueryArguments
{
    private int $globalMemory;
    private string $plannerImpl;
    private int $memory;
    private string $stringRepresentation;
    private string $runtime;
    private string $runtimeImpl;
    private int $dbHits;
    private int $batchSize;
    private string $details;
    private string $plannerVersion;
    private string $pipelineInfo;
    private string $runtimeVersion;
    private int $id;
    private float $estimatedRows;
    private string $planner;
    private int $rows;

    public function __construct(
        ?int $globalMemory = 0,
        ?string $plannerImpl = '',
        ?int $memory = 0,
        ?string $stringRepresentation = '',
        $runtime = '',
        ?string $runtimeImpl = '',
        ?int $dbHits = 0,
        ?int $batchSize = 0,
        ?string $details = '',
        ?string $plannerVersion = '',
        ?string $pipelineInfo = '',
        ?string $runtimeVersion = '',
        ?int $id = 0,
        ?float $estimatedRows = 0.0,
        ?string $planner = '',
        ?int $rows = 0
    ) {
        $this->globalMemory = $globalMemory ?? 0;
        $this->plannerImpl = $plannerImpl ?? '';
        $this->memory = $memory ?? 0;
        $this->stringRepresentation = $stringRepresentation ?? '';
        $this->runtime = is_string($runtime) ? $runtime : json_encode($runtime);
        $this->runtimeImpl = $runtimeImpl ?? '';
        $this->dbHits = $dbHits ?? 0;
        $this->batchSize = $batchSize ?? 0;
        $this->details = $details ?? '';
        $this->plannerVersion = $plannerVersion ?? '';
        $this->pipelineInfo = $pipelineInfo ?? '';
        $this->runtimeVersion = $runtimeVersion ?? '';
        $this->id = $id ?? 0;
        $this->estimatedRows = $estimatedRows ?? 0.0;
        $this->planner = $planner ?? '';
        $this->rows = $rows ?? 0;
    }
    /**
     * @api
     */

    public function getGlobalMemory(): int
    {
        return $this->globalMemory;
    }
    /**
     * @api
     */

    public function getPlannerImpl(): string
    {
        return $this->plannerImpl;
    }
    /**
     * @api
     */

    public function getMemory(): int
    {
        return $this->memory;
    }
    /**
     * @api
     */

    public function getStringRepresentation(): string
    {
        return $this->stringRepresentation;
    }
    /**
     * @api
     */

    public function getRuntime(): string
    {
        return $this->runtime;
    }
    /**
     * @api
     */

    public function getRuntimeImpl(): string
    {
        return $this->runtimeImpl;
    }
    /**
     * @api
     */

    public function getDbHits(): int
    {
        return $this->dbHits;
    }
    /**
     * @api
     */

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }
    /**
     * @api
     */

    public function getDetails(): string
    {
        return $this->details;
    }
    /**
     * @api
     */

    public function getPlannerVersion(): string
    {
        return $this->plannerVersion;
    }
    /**
     * @api
     */

    public function getPipelineInfo(): string
    {
        return $this->pipelineInfo;
    }
    /**
     * @api
     */

    public function getRuntimeVersion(): string
    {
        return $this->runtimeVersion;
    }
    /**
     * @api
     */

    public function getId(): int
    {
        return $this->id;
    }
    /**
     * @api
     */

    public function getEstimatedRows(): float
    {
        return $this->estimatedRows;
    }
    /**
     * @api
     */

    public function getPlanner(): string
    {
        return $this->planner;
    }
    /**
     * @api
     */
    public function getRows(): int
    {
        return $this->rows;
    }
}
