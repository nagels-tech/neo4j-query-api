<?php

namespace Neo4j\QueryAPI\Objects;

class Point
{
    public function __construct(
        public float $longitude,
        public float $latitude,
        public int   $srid
    )
    {
    }


    public function getLongitude(): float
    {
        return $this->longitude;
    }


    public function getLatitude(): float
    {
        return $this->latitude;
    }


    public function getSrid(): int
    {
        return $this->srid;
    }


    public function __toString(): string
    {
        return "SRID={$this->srid};POINT ({$this->longitude} {$this->latitude})";
    }
}
