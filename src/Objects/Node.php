<?php
namespace Neo4j\QueryAPI\Objects;

class Node
{
    private $labels;
    private $properties;

    public function __construct(array $labels, array $properties)
    {
        $this->labels = $labels;
        $this->properties = $properties;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}

