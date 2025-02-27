<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * Represents a relationship in a Neo4j graph, with a type and associated properties.
 */

class Relationship
{
    /**
     * @var string The type of the relationship (e.g., "FRIENDS_WITH", "WORKS_FOR").
     */
    public readonly string $type;

    /**
     * @var array<string, mixed> Associative array of properties for the relationship.
     */
    public readonly array $properties;

    /**
     * Relationship constructor.
     *
     * @param string $type The type of the relationship.
     * @param array<string, mixed> $properties Associative array of properties for the relationship.
     */
    public function __construct(string $type, array $properties = [])
    {
        $this->type = $type;
        $this->properties = $properties;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }


}
