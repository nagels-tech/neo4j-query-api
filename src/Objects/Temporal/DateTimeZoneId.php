<?php

namespace Neo4j\QueryAPI\Objects\Temporal;

final class DateTimeZoneId
{
    private string $zoneId;

    public function __construct(string $zoneId)
    {
        $this->zoneId = $zoneId;
    }

    public function getZoneId(): string
    {
        return $this->zoneId;
    }

    public function __toString(): string
    {
        return $this->zoneId;
    }
}
