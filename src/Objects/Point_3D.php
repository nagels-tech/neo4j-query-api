<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * Represents a geographical 3D point with longitude, latitude, altitude, and SRID (Spatial Reference System Identifier).
 */
class Point_3D
{
    /**
     * @param float $longitude The longitude of the point.
     * @param float $latitude The latitude of the point.
     * @param float $altitude The altitude of the point.
     * @param int $srid The Spatial Reference System Identifier (SRID).
     */
    public function __construct(
        public float $longitude,
        public float $latitude,
        public float $altitude,
        public int   $srid
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

    /**
     * Get the altitude of the point.
     *
     * @return float Altitude value.
     */
    public function getAltitude(): float
    {
        return $this->altitude;
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
     * Convert the Point3D object to a string representation.
     *
     * @return string String representation in the format: "SRID=<srid>;POINT ( <longitude> <latitude> <altitude> )".
     */
    public function __toString(): string
    {
        return "SRID={$this->srid};POINT ({$this->longitude} {$this->latitude} {$this->altitude})";
    }
}