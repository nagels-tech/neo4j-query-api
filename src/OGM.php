<?php

namespace Neo4j\QueryAPI;

use DateTimeZone;
use Neo4j\QueryAPI\Objects\Point;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Relationship;
use Neo4j\QueryAPI\Objects\Path;
use InvalidArgumentException;
use Neo4j\QueryAPI\Objects\Temporal\Date;
use Neo4j\QueryAPI\Objects\Temporal\DateTime;
use Neo4j\QueryAPI\Objects\Temporal\DateTimeZoneId;
use Neo4j\QueryAPI\Objects\Temporal\Duration;
use Neo4j\QueryAPI\Objects\Temporal\LocalDateTime;
use Neo4j\QueryAPI\Objects\Temporal\LocalTime;
use Neo4j\QueryAPI\Objects\Temporal\Time;

final class OGM
{
    /**
     * @param array<array-key, mixed> $data
     * @return mixed
     */
    public function map(array $data): mixed
    {
        if (!isset($data['$type']) || !array_key_exists('_value', $data) || !is_string($data['$type'])) {
            throw new InvalidArgumentException("Unknown object type: " . json_encode($data, JSON_THROW_ON_ERROR));
        }

        return match ($data['$type']) {
            'Integer', 'Float', 'Boolean' => $data['_value'],
            'Array', 'List' => is_array($data['_value']) ? array_map([$this, 'map'], $data['_value']) : [],
            'Null' => null,
            'Node' => $this->mapNode($data['_value']),
            'Map' => is_array($data['_value']) ? $this->mapProperties($data['_value']) : [],
            'Point' => $this->parsePoint($data['_value']),
            'Relationship' => $this->mapRelationship($data['_value']),
            'Path' => $this->mapPath($data['_value']),
            'Date' => $this->mapDate($data['_value']),
            'OffsetDateTime' => $this->mapDateTime($data['_value']),
            'Time' => $this->mapTime($data['_value']),
            'LocalTime' => $this->mapLocalTime($data['_value']),
            'LocalDateTime'=> $this->mapLocalDateTime($data['_value']),
            'Duration'=>$this->mapDuration($data['_value']),

            'String' => $this->isValidTimeZone($data['_value'])
                ? new DateTimeZoneId($data['_value'])  //  Convert timezone strings to `DateTimeZoneId`
                : $data['_value'],
            default => throw new InvalidArgumentException('Unknown type: ' . json_encode($data, JSON_THROW_ON_ERROR)),
        };
    }


    private function parsePoint(string $value): Point
    {
        if (preg_match('/SRID=(\d+);POINT(?: Z)? \(([-\d.]+) ([-\d.]+)(?: ([-\d.]+))?\)/', $value, $matches)) {
            $srid = (int)$matches[1];
            $x = (float)$matches[2];
            $y = (float)$matches[3];
            $z = isset($matches[4]) ? (float)$matches[4] : null;

            return new Point($x, $y, $z, $srid);
        }

        throw new InvalidArgumentException("Invalid Point format: " . $value);
    }


    private function mapNode(array $nodeData): Node
    {
        return new Node(
            labels: $nodeData['_labels'] ?? [],
            properties: $this->mapProperties($nodeData['_properties'] ?? [])
        );
    }

    private function mapRelationship(array $relationshipData): Relationship
    {
        return new Relationship(
            type: $relationshipData['_type'] ?? 'UNKNOWN',
            properties: $this->mapProperties($relationshipData['_properties'] ?? [])
        );
    }


    public static function parseWKT(string $wkt): Point
    {
        $sridPos = strpos($wkt, ';');
        if ($sridPos === false) {
            throw new \InvalidArgumentException("Invalid WKT format: missing ';'");
        }
        $sridPart = substr($wkt, 0, $sridPos);
        $srid = (int)str_replace('SRID=', '', $sridPart);

        $pointPos = strpos($wkt, 'POINT');
        if ($pointPos === false) {
            throw new \InvalidArgumentException("Invalid WKT format: missing 'POINT'");
        }
        $pointPart = substr($wkt, $pointPos + 6);

        $pointPart = str_replace('Z', '', $pointPart);
        $pointPart = trim($pointPart, ' ()');
        $coordinates = explode(' ', $pointPart);

        [$x, $y, $z] = array_pad(array_map('floatval', $coordinates), 3, 0.0);

        return new Point($x, $y, $z, $srid);
    }


    private function mapPath(array $pathData): Path
    {
        $nodes = [];
        $relationships = [];

        foreach ($pathData as $item) {
            if ($item['$type'] === 'Node') {
                $nodes[] = $this->mapNode($item['_value']);
            } elseif ($item['$type'] === 'Relationship') {
                $relationships[] = $this->mapRelationship($item['_value']);
            }
        }

        return new Path($nodes, $relationships);
    }

    private function mapProperties(array $properties): array
    {

        $mappedProperties = [];

        foreach ($properties as $key => $value) {
            if (is_array($value) && isset($value['$type'], $value['_value'])) {
                $mappedProperties[$key] = $this->map($value);
            } elseif (is_scalar($value)) {
                $mappedProperties[$key] = $value;
            } elseif (is_array($value) && !isset($value['$type'])) {
                $mappedProperties[$key] = $this->map(['$type' => 'Map', '_value' => $value]);
            } else {
                error_log("Invalid property format for key: {$key} => " . json_encode($value, JSON_THROW_ON_ERROR));

                throw new \InvalidArgumentException("Invalid property format for key: {$key}");
            }
        }

        return $mappedProperties;
    }

    private function mapDate(string $value)
    {
        $totalDaysSinceEpoch = (new \DateTime($value))->diff(new \DateTime('@0'))->days;

        return new Date($totalDaysSinceEpoch);
    }

    private function mapDateTime(string $value)
    {
        return new DateTime($value);
    }

    private function mapDateTimeZoneId(string $value)
    {
        return new DateTimeZoneId($value);
    }

    private function isValidTimeZone(string $value): bool
    {
        return in_array($value, timezone_identifiers_list(), true);
    }

    private function mapTime(mixed $_value)
    {
        return new Time($_value);

    }

    private function mapLocalTime(mixed $_value)
    {
        return new LocalTime($_value);
    }

    private function mapLocalDateTime(mixed $_value)
    {
         return new LocalDateTime($_value);
    }

    private function mapDuration(mixed $_value)
    {
         return new Duration($_value);
    }
}
