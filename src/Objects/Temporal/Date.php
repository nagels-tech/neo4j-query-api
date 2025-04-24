<?php

namespace Neo4j\QueryAPI\Objects\Temporal;

final class Date
{
    public int $days;

    public function __construct(int $days)
    {
        $this->days = $days;
    }

    /**
     * Returns the number of days since the Unix epoch.
     */
    public function getDays(): int
    {
        return $this->days;
    }

    /**
     * Converts the stored date into a DateTimeImmutable.
     */
    public function toDateTimeImmutable(): \DateTimeImmutable
    {
        $dt = new \DateTimeImmutable('@0');
        return $dt->modify(sprintf('+%d days', $this->days));
    }

    public function __toString(): string
    {
        return sprintf("Date(%d days since epoch)", $this->days);
    }
}
