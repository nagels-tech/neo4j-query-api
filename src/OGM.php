<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Objects\Point;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Relationship;
use Neo4j\QueryAPI\Objects\Path;

class OGM
{
    /**
     * @param array{'$type': string, ' $object _value': mixed} $object
     * @return mixed
     */
    public function map(array $object): mixed
    {
        return match ($object['$type']) {
            'Integer' => $object['_value'],
            'Float' => $object['_value'],
            'String' => $object['_value'],
            'Boolean' => $object['_value'],
            'Null' => $object['_value'],
            'Array' => $object['_value'],
            'List' => array_map([$this, 'map'], $object['_value']),
            'Duration' => $object['_value'],
            'OffsetDateTime' => $object['_value'],
            'Node' => $this->mapNode($object['_value']),
            'Map' => $this->mapProperties($object['_value']),
            'Point' => $this->parseWKT($object['_value']),
            'Relationship' => $this->mapRelationship($object['_value']),
            'Path' => $this->mapPath($object['_value']),
            default => throw new \InvalidArgumentException('Unknown type: ' . $object['$type']),
        };
    }

    public static function parseWKT(string $wkt): Point
    {
        $sridPart = substr($wkt, 0, strpos($wkt, ';'));
        $srid = (int)str_replace('SRID=', '', $sridPart);

        $pointPart = substr($wkt, strpos($wkt, 'POINT') + 6);
        if (str_contains($pointPart, 'Z')) {
            $pointPart = str_replace('Z', '', $pointPart);
        }
        $pointPart = trim($pointPart, ' ()');
        $coordinates = explode(' ', $pointPart);

        if (count($coordinates) === 2) {
            [$x, $y] = $coordinates;
            $z = null;
        } elseif (count($coordinates) === 3) {
            [$x, $y, $z] = $coordinates;
        } else {
            throw new \InvalidArgumentException("Invalid WKT format: unable to parse coordinates.");
        }

        return new Point((float)$x, (float)$y, $z !== null ? (float)$z : null, $srid);
    }




    private function mapNode(array $nodeData): Node
    {
        return new Node(
            $nodeData['_labels'],
            $this->mapProperties($nodeData['_properties'])
        );
    }


    private function mapRelationship(array $relationshipData): Relationship
    {
        return new Relationship(
            $relationshipData['_type'],
            $this->mapProperties($relationshipData['_properties'])
        );
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
            $mappedProperties[$key] = $this->map($value);
        }
        return $mappedProperties;
    }

}
