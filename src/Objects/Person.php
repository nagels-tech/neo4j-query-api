<?php
namespace Neo4j\QueryAPI\Objects;

class Person extends Node
{
    public function __construct(array $properties)
    {
        parent::__construct(['Person'], $properties); // Pass the labels and properties correctly
    }
}
