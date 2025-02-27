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
final class ResultRow implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var array<string, TValue> */
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    #[\Override]
    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Column {$offset} not found.");
        }
        return $this->data[$offset];
    }


    #[\Override]
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    #[\Override]
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException("You can't set the value of column {$offset}.");
    }
    #[\Override]
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException("You can't Unset {$offset}.");

    }

    #[\Override]
    public function count(): int
    {
        return count($this->data);
    }

    #[\Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
