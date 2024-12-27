<?php
namespace Neo4j\QueryAPI\Results;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Neo4j\QueryAPI\OGM;
use Traversable;

class ResultSet implements IteratorAggregate, Countable
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(private readonly array $rows)
    {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rows);
    }

    public function count(): int
    {
        return count($this->rows);
    }


}
