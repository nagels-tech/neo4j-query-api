<?php
namespace Neo4j\QueryAPI\Results;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Traversable;

class ResultSet implements IteratorAggregate, Countable
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(private readonly array $rows,private ResultCounters $counters)
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

    public function count(): int
    {
        return count($this->rows);
    }


}
