<?php

namespace Neo4j\QueryAPI\Results;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Neo4j\QueryAPI\Enums\AccessMode;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\ResultCounters;
use OutOfBoundsException;
use Traversable;

/**
 * @template TValue
 * @implements IteratorAggregate<int, ResultRow>
 */
final class ResultSet implements IteratorAggregate, Countable, ArrayAccess
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(
        public readonly array              $rows,
        public readonly Bookmarks          $bookmarks,
        public readonly AccessMode         $accessMode,
        public readonly ?ResultCounters    $counters = null,
        public readonly ?ProfiledQueryPlan $profiledQueryPlan = null
    )
    {
    }

    /**
     * @return Traversable<int, ResultRow>
     */
    #[\Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rows);
    }


    #[\Override]
    public function count(): int
    {
        return count($this->rows);
    }

    public function get(int $index): ResultRow
    {
        if (!isset($this->rows[$index])) {
            throw new OutOfBoundsException('Index ' . $index . ' does not exist');
        }
        return $this->rows[$index];

    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->rows[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->rows[$offset] ?? throw new \OutOfBoundsException("Index $offset is out of bounds.");
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException("ResultSet is immutable. You cannot modify elements.");
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException("ResultSet is immutable. You cannot remove elements.");
    }
}
