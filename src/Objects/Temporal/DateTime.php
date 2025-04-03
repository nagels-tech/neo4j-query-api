<?php

namespace Neo4j\QueryAPI\Objects\Temporal;

final class DateTime
{
    private \DateTimeImmutable $dateTime;

    public function __construct(string $dateTime)
    {
        $this->dateTime = new \DateTimeImmutable($dateTime);
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function __toString(): string
    {
        return $this->dateTime->format('c');
    }
}
