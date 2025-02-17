<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Objects\Point;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Relationship;
use Neo4j\QueryAPI\Objects\Path;

/**
 *  @api
 */
class OGM
{
    /**
     * @param array{'$type': string, '_value': mixed} $object
     * @return mixed
     */
    public function map(array $object): mixed
    {

        if (!isset($object['$type'])) {
            if (array_key_exists('elementId', $object) && array_key_exists('labels', $object) && array_key_exists('properties', $object)) {
                return $this->mapNode($object);
            }
            throw new \InvalidArgumentException('Unknown object type: ' . json_encode($object, JSON_THROW_ON_ERROR));
        }

        //        if (!isset($object['_value'])) {
        //            throw new \InvalidArgumentException('Missing _value key in object: ' . json_encode($object));
        //        }

        return match ($object['$type']) {
            'Integer', 'Float', 'String', 'Boolean', 'Duration', 'OffsetDateTime' => $object['_value'],
            'Array' => $object['_value'],
            'Null' => null,
            'List' => array_map([$this, 'map'], $object['_value']),
            'Node' => $this->mapNode($object['_value']),
            'Map' => $this->mapProperties($object['_value']),
            'Point' => $this->parseWKT($object['_value']),
            'Relationship' => $this->mapRelationship($object['_value']),
            'Path' => $this->mapPath($object['_value']),
            default => throw new \InvalidArgumentException('Unknown type: ' . $object['$type'] . ' in object: ' . json_encode($object, JSON_THROW_ON_ERROR)),
        };
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
            type: $relationshipData['_type'] ?? '',
            properties: $this->mapProperties($relationshipData['_properties'] ?? [])
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
        return array_map([$this, 'map'], $properties);
    }

}
