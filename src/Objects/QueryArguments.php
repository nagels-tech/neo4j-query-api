<?php

namespace Neo4j\QueryAPI\Objects;

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
        ?string $runtime = '',
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
        $this->runtime = $runtime ?? '';
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

    public function getRuntimeImpl(): string
    {
        return $this->runtimeImpl;
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
