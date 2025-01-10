<?php

namespace Neo4j\QueryAPI\Results;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Neo4j\QueryAPI\Objects\ChildQueryPlan;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlanArguments;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\Bookmarks;  // Make sure to include the Bookmarks class
use Traversable;

class ResultSet implements IteratorAggregate, Countable
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(
        private readonly array $rows,
        private ResultCounters $counters,
        private Bookmarks $bookmarks,
        private ?ProfiledQueryPlan $profiledQueryPlan = null
    )
    {
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

    public function getChildQueryPlan(): ?ChildQueryPlan
    {
         return $this->childQueryPlan;
    }

    public function count(): int
    {
        return count($this->rows);
    }

    public function getBookmarks(): ?Bookmarks
    {
        return $this->bookmarks;
    }
}
