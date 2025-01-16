<?php

namespace Neo4j\QueryAPI\Results;

use BadMethodCallException;
use Neo4j\QueryAPI\OGM;
use OutOfBoundsException;
use ArrayAccess;

class ResultRow implements ArrayAccess
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


    public function get(string $row): mixed
    {
        return $this->offsetGet($row);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
