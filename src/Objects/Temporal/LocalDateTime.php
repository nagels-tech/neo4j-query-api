<?php

namespace Neo4j\QueryAPI\Objects\Temporal;

final class LocalDateTime
{
    private \DateTimeImmutable $localDateTime;

    public function __construct(string $localDateTime)
    {
        $this->localDateTime = new \DateTimeImmutable($localDateTime);
    }

    public function getLocalDateTime(): \DateTimeImmutable
    {
        return $this->localDateTime;
    }

    public function __toString(): string
    {
        // Adjust the format as necessary (without timezone info)
        return $this->localDateTime->format('Y-m-d\TH:i:s');
    }
}
