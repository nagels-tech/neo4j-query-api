<?php

namespace Neo4j\QueryAPI\Objects;

class Point
{
    public function __construct(
        public float $longitude,
        public float $latitude,
        public string $crs
    )
    {
    }
}