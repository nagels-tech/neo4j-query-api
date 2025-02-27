<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * Represents a Neo4j Node with labels and properties.
 */

class Node
{
    /**
     * Node constructor
     *
     * @param string[] $labels Array of labels for the node.
     * @param array<string, mixed> $properties Associative array of properties.
     */
    public function __construct(public readonly array $labels, public readonly array $properties)
    {
    }

    /**
     * Convert the Node object to an array representation.
     * @return array Node data as an array.
     */
    public function toArray(): array
    {
        return [
            '_labels' => $this->labels,
            '_properties' => $this->properties,
        ];
    }
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }
}
