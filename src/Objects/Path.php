<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * Represents a path in a Neo4j graph, consisting of nodes and relationships.
 */

class Path
{
    /**
     * @var Node[] Array of nodes in the path.
     */
    public readonly array $nodes;

    /**
     * @var Relationship[] Array of relationships in the path.
     */
    public readonly array $relationships;

    /**
     * Path constructor.
     *
     * @param Node[] $nodes Array of nodes in the path.
     * @param Relationship[] $relationships Array of relationships in the path.
     */
    public function __construct(array $nodes, array $relationships)
    {
        $this->nodes = $nodes;
        $this->relationships = $relationships;
    }

}
