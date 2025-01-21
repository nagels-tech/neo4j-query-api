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
    private ProfiledQueryPlanArguments $arguments;

    /**
     * @var list<ProfiledQueryPlan|ProfiledQueryPlanArguments>
     */
    private array $children;

    /**
     * @var string[]
     */
    private array $identifiers;

    public function __construct(
        ?int $dbHits,
        ?int $records,
        ?bool $hasPageCacheStats,
        ?int $pageCacheHits,
        ?int $pageCacheMisses,
        ?float $pageCacheHitRatio,
        ?int $time,
        ?string $operatorType,
        ProfiledQueryPlanArguments $arguments,
        ?array $children = [],
        array $identifiers = [] // Default to an empty array
    ) {
        $this->dbHits = $dbHits ?? 0;
        $this->records = $records ?? 0;
        $this->hasPageCacheStats = $hasPageCacheStats ?? false;
        $this->pageCacheHits = $pageCacheHits ?? 0;
        $this->pageCacheMisses = $pageCacheMisses ?? 0;
        $this->pageCacheHitRatio = $pageCacheHitRatio ?? 0.0;
        $this->time = $time ?? 0;
        $this->operatorType = $operatorType ?? '';
        $this->arguments = $arguments;
        $this->children = $children ?? [];
        $this->identifiers = $identifiers;
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

    public function getArguments(): ProfiledQueryPlanArguments
    {
        return $this->arguments;
    }

    /**
     * @return list<ProfiledQueryPlan|ProfiledQueryPlanArguments>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(ProfiledQueryPlan|ProfiledQueryPlanArguments $child): void
    {
        $this->children[] = $child;
    }

    /**
     * @return string[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @param string[] $identifiers
     */
    public function setIdentifiers(array $identifiers): void
    {
        $this->identifiers = $identifiers;
    }

    public function addIdentifier(string $identifier): void
    {
        $this->identifiers[] = $identifier;
    }
}
