<?php

namespace Neo4j\QueryAPI\Objects;

class ProfiledQueryPlan
{
    private int $dbHits;
    private int $records;
    private bool $hasPageCacheStats;
    private int $pageCacheHits;
    private int $pageCacheMisses;
    private float $pageCacheHitRatio;
    private int $time;
    private string $operatorType;
    private array $arguments;

    public function __construct(
        ?int $dbHits = 0, // Default to 0 if null
        ?int $records = 0,
        ?bool $hasPageCacheStats = false,
        ?int $pageCacheHits = 0,
        ?int $pageCacheMisses = 0,
        ?float $pageCacheHitRatio = 0.0,
        ?int $time = 0,
        ?string $operatorType = '',
        ?array $arguments = []
    ) {
        $this->dbHits = $dbHits ?? 0;
        $this->records = $records ?? 0;
        $this->hasPageCacheStats = $hasPageCacheStats ?? false;
        $this->pageCacheHits = $pageCacheHits ?? 0;
        $this->pageCacheMisses = $pageCacheMisses ?? 0;
        $this->pageCacheHitRatio = $pageCacheHitRatio ?? 0.0;
        $this->time = $time ?? 0;
        $this->operatorType = $operatorType ?? '';
        $this->arguments = $arguments ?? [];
    }

    public function getDbHits(): int
    {
        return $this->dbHits;
    }

    public function getRecords(): int
    {
        return $this->records;
    }

    public function hasPageCacheStats(): bool
    {
        return $this->hasPageCacheStats;
    }

    public function getPageCacheHits(): int
    {
        return $this->pageCacheHits;
    }

    public function getPageCacheMisses(): int
    {
        return $this->pageCacheMisses;
    }

    public function getPageCacheHitRatio(): float
    {
        return $this->pageCacheHitRatio;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getOperatorType(): string
    {
        return $this->operatorType;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
