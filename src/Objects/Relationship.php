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
    private string $type;

    /**
     * @var array<string, mixed> Associative array of properties for the relationship.
     */
    private array $properties;

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

    /**
     * Get the type of the relationship.
     *
     * @return string The type of the relationship.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the properties of the relationship.
     *
     * @return array<string, mixed> Associative array of properties.
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
