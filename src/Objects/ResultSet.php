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

/**
 *  @api
 */
/**
 * @template TKey
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
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
   
    public function getQueryCounters(): ?ResultCounters
    {
        return $this->counters;
    }
   
    public function getProfiledQueryPlan(): ?ProfiledQueryPlan
    {
        return $this->profiledQueryPlan;
    }
    /**
     *  @api
     */
    public function count(): int
    {
        return count($this->rows);
    }
   
    public function getBookmarks(): ?Bookmarks
    {
        return $this->bookmarks;
    }
}
