<?php

namespace Neo4j\QueryAPI\Objects;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Neo4j\QueryAPI\Results\ResultRow;
use phpDocumentor\Reflection\DocBlock\Tags\Template;
use Traversable;

/**
 * @template-implements IteratorAggregate<int, ResultRow>
 */
class ResultSet implements IteratorAggregate, Countable
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(
        private readonly array $rows,
        private ResultCounters $counters,
        private Bookmarks $bookmarks,
        private ?ProfiledQueryPlan $profiledQueryPlan = null,
        // private ?ProfiledQueryPlanArguments $profiledQueryPlanArguments = null
    ) {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rows);
    }
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getQueryCounters(): ?ResultCounters
    {
        return $this->counters;
    }
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getProfiledQueryPlan(): ?ProfiledQueryPlan
    {
        return $this->profiledQueryPlan;
    }
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function count(): int
    {
        return count($this->rows);
    }
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getBookmarks(): ?Bookmarks
    {
        return $this->bookmarks;
    }
}
