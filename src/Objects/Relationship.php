<?php


namespace Neo4j\QueryAPI\Objects;

class Relationship
{
    private $type;
    private $properties;

    public function __construct($type, array $properties = [])
    {
        $this->type = $type;
        $this->properties = $properties;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getProperties()
    {
        return $this->properties;
    }
}
