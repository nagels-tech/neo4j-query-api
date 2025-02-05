<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Objects\Point;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Relationship;
use Neo4j\QueryAPI\Objects\Path;

class OGM
{
    /**
     * Map Neo4j response object to corresponding PHP object.
     *
     * @param array{'$type': string, '_value': mixed} $object
     * @return mixed Mapped object or primitive value.
     */
    public function map(array $object): mixed
    {
        if (!isset($object['$type'])) {
            if (isset($object['elementId'], $object['labels'], $object['properties'])) {
                return $this->mapNode($object); // Handle as a Node
            }
            throw new \InvalidArgumentException('Unknown object type: ' . json_encode($object));
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
            default => throw new \InvalidArgumentException('Unknown type: ' . $object['$type'] . ' in object: ' . json_encode($object)),
        };
    }


    /**
     * Parse Well-Known Text (WKT) format to a Point object.
     *
     * @param string $wkt Well-Known Text representation of a point.
     * @return Point Parsed Point object.
     */
    public static function parseWKT(string $wkt): Point
    {
        // Extract SRID
        $sridPart = substr($wkt, 0, strpos($wkt, ';'));
        $srid = (int)str_replace('SRID=', '', $sridPart);

        // Extract coordinates
        $pointPart = substr($wkt, strpos($wkt, 'POINT') + 6);
        $pointPart = str_replace('Z', '', trim($pointPart, ' ()'));
        $coordinates = explode(' ', $pointPart);

        [$x, $y, $z] = array_pad(array_map('floatval', $coordinates), 3, null);

        return new Point($x, $y, $z, $srid);
    }

    /**
     * Map a raw node data array to a Node object.
     *
     * @param array $nodeData Raw node data.
     * @return Node Mapped Node object.
     */
    private function mapNode(array $nodeData): Node
    {
        return new Node(
            labels: $nodeData['_labels'] ?? [],
            properties: $this->mapProperties($nodeData['_properties'] ?? [])
        );
    }

    /**
     * Map a raw relationship data array to a Relationship object.
     *
     * @param array $relationshipData Raw relationship data.
     * @return Relationship Mapped Relationship object.
     */
    private function mapRelationship(array $relationshipData): Relationship
    {
        return new Relationship(
            type: $relationshipData['_type'] ?? '',
            properties: $this->mapProperties($relationshipData['_properties'] ?? [])
        );
    }

    /**
     * Map a raw path data array to a Path object.
     *
     * @param array $pathData Raw path data.
     * @return Path Mapped Path object.
     */
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

    /**
     * Recursively map properties of a node or relationship.
     *
     * @param array $properties Raw properties data.
     * @return array Mapped properties.
     */
    private function mapProperties(array $properties): array
    {
        return array_map([$this, 'map'], $properties);
    }
}
