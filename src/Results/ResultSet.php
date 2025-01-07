<?php
namespace Neo4j\QueryAPI\Results;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Neo4j\QueryAPI\Objects\ChildQueryPlan;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\QueryArguments;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Traversable;

class ResultSet implements IteratorAggregate, Countable
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(private readonly array $rows, private ResultCounters $counters, private ?ProfiledQueryPlan $profiledQueryPlan = null)
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

    public function getQueryArguments(): ?QueryArguments
    {
        return $this->queryArguments;
    }

    public function getChildQueryPlan(): ?ChildQueryPlan
    {
         return $this->childQueryPlan;
    }

    public function count(): int
    {
        return count($this->rows);
    }
}

