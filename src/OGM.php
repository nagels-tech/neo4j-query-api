<?php
namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Objects\Point;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Relationship;
use Neo4j\QueryAPI\Objects\Path;

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
            'Null' => $object['_value'],
            'Array' => $object['_value'],
            'Duration' => $object['_value'],
            'OffsetDateTime' => $object['_value'],
            'Point' => $this->parseWKT($object['_value']),
            'Node' => $this->mapNode($object['_value']),
            'Relationship' => $this->mapRelationship($object['_value']),
            'Path' => $this->mapPath($object['_value']),
            default => throw new \InvalidArgumentException('Unknown type: ' . $object['$type']),
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

    private function mapNode(array $nodeData): Node
    {
        return new Node(
            $nodeData['_labels'], // Node labels
            $this->mapProperties($nodeData['_properties']) // Node properties
        );
    }

    private function mapRelationship(array $relationshipData): Relationship
    {
        return new Relationship(
            $relationshipData['_type'], // Relationship type
            $this->mapProperties($relationshipData['_properties']) // Relationship properties
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
        // Map properties to their raw values (no special parsing)
        $mappedProperties = [];
        foreach ($properties as $key => $value) {
            $mappedProperties[$key] = $this->map($value);
        }
        return $mappedProperties;
    }
}
