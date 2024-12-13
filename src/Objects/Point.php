<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * Represents a geographical point with longitude, latitude, and SRID (Spatial Reference System Identifier).
 */
class Point
{
    /**
     * @param float $longitude The longitude of the point.
     * @param float $latitude The latitude of the point.
     * @param int $srid The Spatial Reference System Identifier (SRID).
     */
    public function __construct(
        public float $longitude,
        public float $latitude,
        public float $height,
        public float $x,
        public float $y,
        public float $z,
        public int   $srid,
    ) {
    }

    /**
     * Get the longitude of the point.
     *
     * @return float Longitude value.
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Get the latitude of the point.
     *
     * @return float Latitude value.
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }
    public function getHeight(): float
    {
        return $this->height;
    }
    /**
     * Get the x of the point.
     *
     * @return float x value.
     */
    public function getX(): float
    {
        return $this->x;
    }
    /**
     * Get the y of the point.
     *
     * @return float y value.
     */
    public function getY(): float
    {
        return $this->y;
    }

    /**
     * Get the z of the point.
     *
     * @return float z value.
     */
    public function getZ(): float
    {
        return $this->z;
    }


    /**
     * Get the SRID (Spatial Reference System Identifier) of the point.
     *
     * @return int SRID value.
     */
    public function getSrid(): int
    {
        return $this->srid;
    }

    /**
     * Convert the Point object to a string representation.
     *
     * @return string String representation in the format: "SRID=<srid>;POINT (<longitude> <latitude>)".
     */
    public function __toString(): string
    {
        return "SRID={$this->srid};POINT ({$this->longitude} {$this->latitude} {$this->x} {$this->y})";
    }
}
