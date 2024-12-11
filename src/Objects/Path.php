<?php


namespace Neo4j\QueryAPI\Objects;

class Path
{
    private $nodes;
    private $relationships;

    public function __construct(array $nodes, array $relationships)
    {
        $this->nodes = $nodes;
        $this->relationships = $relationships;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getRelationships()
    {
        return $this->relationships;
    }
}