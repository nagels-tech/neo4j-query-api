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
    private QueryArguments $arguments;

    /**
     * @var list<ProfiledQueryPlan>
     */
    private array $children;

    public function __construct(
        ?int $dbHits = 0, // Default to 0 if null
        ?int $records = 0,
        ?bool $hasPageCacheStats = false,
        ?int $pageCacheHits = 0,
        ?int $pageCacheMisses = 0,
        ?float $pageCacheHitRatio = 0.0,
        ?int $time = 0,
        ?string $operatorType = '',
        QueryArguments $arguments
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

    public function getRecords(): int
    {
        return $this->records;
    }
    /**
     * @api
     */

    public function hasPageCacheStats(): bool
    {
        return $this->hasPageCacheStats;
    }
    /**
     * @api
     */

    public function getPageCacheHits(): int
    {
        return $this->pageCacheHits;
    }
    /**
     * @api
     */

    public function getPageCacheMisses(): int
    {
        return $this->pageCacheMisses;
    }
    /**
     * @api
     */

    public function getPageCacheHitRatio(): float
    {
        return $this->pageCacheHitRatio;
    }
    /**
     * @api
     */

    public function getTime(): int
    {
        return $this->time;
    }
    /**
     * @api
     */

    public function getOperatorType(): string
    {
        return $this->operatorType;
    }
    /**
     * @api
     */

    public function getArguments(): QueryArguments
    {
        return $this->arguments;
    }

    /**
     * @api
     * @return list<ProfiledQueryPlan>
     */
    public  function getChildren(): array
    {
        return $this->children;
    }
    /**
     * @api
     */

    public function addChild(ProfiledQueryPlan $child): void
    {
        $this->children[] = $child;
    }
}
