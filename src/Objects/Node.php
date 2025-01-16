<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * Represents a Neo4j Node with labels and properties.
 */
class Node
{
    /**
     * @var string[] Array of labels for the node.
     */
    private array $labels;

    /**
     * @var array<string, mixed> Associative array of properties (key-value pairs).
     */
    private array $properties;

    /**
     * Node constructor.
     *
     * @param string[] $labels Array of labels for the node.
     * @param array<string, mixed> $properties Associative array of properties.
     */
    public function __construct(array $labels, array $properties)
    {
        $this->labels = $labels;
        $this->properties = $properties;
    }

    /**
     * Get the labels of the node.
     *
     * @return string[] Array of labels.
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Get the properties of the node.
     *
     * @return array<string, mixed> Associative array of properties.
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Convert the Node object to an array representation.
     *
     * @return array Node data as an array.
     */
    public function toArray(): array
    {
        return [
            '_labels' => $this->labels,
            '_properties' => $this->properties,
        ];
    }
}
