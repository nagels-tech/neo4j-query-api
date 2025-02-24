<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * Represents a point with x, y, z coordinates, and SRID (Spatial Reference System Identifier).
 */


final class Point
{
    /**
     * @param float $x The x coordinate of the point.
     * @param float $y The y coordinate of the point.
     * @param float|null $z The z coordinate of the point, or null if not applicable.
     * @param int $srid The Spatial Reference System Identifier (SRID).
     */
    public function __construct(
        public float $x,
        public float $y,
        public float|null $z,
        public int $srid,
    ) {
    }

    /**
     * Get the x coordinate of the point.
     * @return float x coordinate value.
     */
    public function getX(): float
    {
        return $this->x;
    }

    /**
     * Get the y coordinate of the point.
     * @return float y coordinate value.
     */
    public function getY(): float
    {
        return $this->y;
    }

    /**
     * Get the z coordinate of the point.
     * @return float|null z coordinate value, or null if not applicable.
     */
    public function getZ(): float|null
    {
        return $this->z;
    }

    /**
     * Get the SRID (Spatial Reference System Identifier) of the point.
     * @return int SRID value.
     */
    public function getSrid(): int
    {
        return $this->srid;
    }

    /**
     * Convert the Point object to a string representation.
     * @return string String representation in the format: "SRID=<srid>;POINT (<x> <y> <z>)".
     */
    public function __toString(): string
    {
        return "SRID={$this->srid};POINT ({$this->x} {$this->y})";
    }
}
