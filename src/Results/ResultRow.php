<?php

namespace Neo4j\QueryAPI\Results;

use BadMethodCallException;
use IteratorAggregate;
use OutOfBoundsException;
use ArrayAccess;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 */
class ResultRow implements ArrayAccess, \Countable, IteratorAggregate
{
    public function __construct(private array $data)
    {

    }


    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Column {$offset} not found.");
        }
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException("You can't set the value of column {$offset}.");
    }
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException("You can't Unset {$offset}.");

    }
    /**
     * @api
     */

    public function get(string $row): mixed
    {
        return $this->offsetGet($row);
    }


    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->data);
    }
}
