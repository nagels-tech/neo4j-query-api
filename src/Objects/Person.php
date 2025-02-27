<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * @psalm-suppress UnusedClass
 * Represents a Person node in the Neo4j graph.
 */
class Person extends Node
{
    /**
     * Person constructor.
     *
     * @param array<string, mixed> $properties Associative array of properties for the Person node.
     */
    public function __construct(array $properties)
    {
        parent::__construct(['Person'], $properties);
    }
}
