<?php
namespace Neo4j\QueryAPI\Results;

use InvalidArgumentException;
use IteratorAggregate;
use Neo4j\QueryAPI\OGM;
use Traversable;

class ResultSet implements IteratorAggregate
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(private readonly array $rows)
    {
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->rows);
    }
}
