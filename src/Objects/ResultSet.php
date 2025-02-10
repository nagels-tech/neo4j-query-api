<?php

namespace Neo4j\QueryAPI\Objects;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Neo4j\QueryAPI\Results\ResultRow;
use Traversable;

// Make sure to include the Bookmarks class

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
        private ?ProfiledQueryPlanArguments $profiledQueryPlanArguments = null
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
