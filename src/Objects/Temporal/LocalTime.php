<?php

namespace Neo4j\QueryAPI\Objects\Temporal;

final class LocalTime
{
    private string $localTime;

    public function __construct(string $localTime)
    {
        $this->localTime = $localTime;
    }

    public function getLocalTime(): string
    {
        return $this->localTime;
    }

    public function __toString(): string
    {
        return $this->localTime;
    }
}
