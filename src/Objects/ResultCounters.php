<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * @api
 */
class ResultCounters
{
    public function __construct(
        private readonly bool $containsUpdates = false,
        private readonly int  $nodesCreated = 0,
        private readonly int  $nodesDeleted = 0,
        private readonly int  $propertiesSet = 0,
        private readonly int  $relationshipsCreated = 0,
        private readonly int  $relationshipsDeleted = 0,
        private readonly int  $labelsAdded = 0,
        private readonly int  $labelsRemoved = 0,
        private readonly int  $indexesAdded = 0,
        private readonly int  $indexesRemoved = 0,
        private readonly int  $constraintsAdded = 0,
        private readonly int $constraintsRemoved = 0,
        private readonly bool $containsSystemUpdates = false,
        private readonly int $systemUpdates = 0
    ) {
    }




    public function ContainsSystemUpdates(): bool
    {
        return $this->containsSystemUpdates;
    }


    public function containsUpdates(): bool
    {
        return $this->containsUpdates;
    }

    public function getNodesCreated(): int
    {
        return $this->nodesCreated;
    }


    public function getNodesDeleted(): int
    {
        return $this->nodesDeleted;
    }


    public function getPropertiesSet(): int
    {
        return $this->propertiesSet;
    }

    public function getRelationshipsCreated(): int
    {
        return $this->relationshipsCreated;
    }

    public function getRelationshipsDeleted(): int
    {
        return $this->relationshipsDeleted;
    }

    public function getLabelsAdded(): int
    {
        return $this->labelsAdded;
    }

    public function getIndexesAdded(): int
    {
        return $this->indexesAdded;
    }

    public function getIndexesRemoved(): int
    {
        return $this->indexesRemoved;
    }

    public function getConstraintsAdded(): int
    {
        return $this->constraintsAdded;
    }

    public function getConstraintsRemoved(): int
    {
        return $this->constraintsRemoved;
    }

    public function getSystemUpdates(): int
    {
        return $this->systemUpdates;
    }

    public function getLabelsRemoved(): int
    {
        return $this->labelsRemoved;
    }
}


