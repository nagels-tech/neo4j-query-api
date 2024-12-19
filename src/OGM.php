<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Objects\Point;

class OGM
{
    /**
     * @param array{'$type': string, '_value': mixed} $object
     * @return mixed
     */
    public function map(array $object): mixed
    {
        return match ($object['$type']) {
            'Integer' => $object['_value'],
            'String' => $object['_value'],
            'Boolean' => $object['_value'],
            'Point' => $this->parseWKT($object['_value']),
            default => $object['_value'],
        };
    }

    private function parseWKT(string $wkt): Point
    {
        $sridPart = substr($wkt, 0, strpos($wkt, ';'));
        $srid = (int)str_replace('SRID=', '', $sridPart);

        $pointPart = substr($wkt, strpos($wkt, 'POINT') + 6);
        $pointPart = trim($pointPart, ' ()');

        list($longitude, $latitude) = explode(' ', $pointPart);

        return new Point((float)$longitude, (float)$latitude, $srid);
    }
}