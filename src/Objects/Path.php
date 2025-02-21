<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * Represents a path in a Neo4j graph, consisting of nodes and relationships.
 */


final class Path
{
    /**
     * @var Node[] Array of nodes in the path.
     */
    private array $nodes;

    /**
     * @var Relationship[] Array of relationships in the path.
     */
    private array $relationships;

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

    /**
     * Get the nodes in the path.
     * @return Node[] Array of nodes.
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Get the relationships in the path.
     * @return Relationship[] Array of relationships.
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }
}
