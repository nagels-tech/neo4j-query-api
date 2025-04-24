<?php

namespace Neo4j\QueryAPI\Objects\Temporal;

final class Time
{
    private string $time; // You might later parse this into components if needed.

    public function __construct(string $time)
    {
        $this->time = $time;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function __toString(): string
    {
        return $this->time;
    }
}
