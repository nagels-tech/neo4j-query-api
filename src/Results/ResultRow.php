<?php

namespace Neo4j\QueryAPI\Results;

use ArrayIterator;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use ArrayAccess;
use Traversable;
/**
 * @template TValue
 * @implements ArrayAccess<string, TValue>
 * @implements IteratorAggregate<string, TValue>
 */
class ResultRow implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var array<string, TValue> */
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Column {$offset} not found.");
        }
        return $this->data[$offset];
    }

    public function get(string $row): mixed
    {
        return $this->offsetGet($row);
    }



    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException("You can't set the value of column {$offset}.");
    }
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException("You can't Unset {$offset}.");

    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}