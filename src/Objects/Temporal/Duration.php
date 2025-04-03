<?php

namespace Neo4j\QueryAPI\Objects\Temporal;

final class Duration
{
    private string $duration;

    public function __construct(string $duration)
    {
        $this->duration = $duration;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function __toString(): string
    {
        return $this->duration;
    }
}
