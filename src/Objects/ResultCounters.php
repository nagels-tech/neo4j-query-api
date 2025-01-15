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



    /**
     * @api
     */
    public function ContainsSystemUpdates(): bool
    {
        return $this->containsSystemUpdates;
    }

    /**
     * @api
     */
    public function containsUpdates(): bool
    {
        return $this->containsUpdates;
    }
    /**
     * @api
     */
    public function getNodesCreated(): int
    {
        return $this->nodesCreated;
    }
    /**
     * @api
     */

    public function getNodesDeleted(): int
    {
        return $this->nodesDeleted;
    }
    /**
     * @api
     */

    public function getPropertiesSet(): int
    {
        return $this->propertiesSet;
    }
    /**
     * @api
     */
    public function getRelationshipsCreated(): int
    {
        return $this->relationshipsCreated;
    }
    /**
     * @api
     */
    public function getRelationshipsDeleted(): int
    {
        return $this->relationshipsDeleted;
    }
    /**
     * @api
     */
    public function getLabelsAdded(): int
    {
        return $this->labelsAdded;
    }
    /**
     * @api
     */
    public function getIndexesAdded(): int
    {
        return $this->indexesAdded;
    }
    /**
     * @api
     */
    public function getIndexesRemoved(): int
    {
        return $this->indexesRemoved;
    }
    /**
     * @api
     */
    public function getConstraintsAdded(): int
    {
        return $this->constraintsAdded;
    }
    /**
     * @api
     */
    public function getConstraintsRemoved(): int
    {
        return $this->constraintsRemoved;
    }
    /**
     * @api
     */
    public function getSystemUpdates(): int
    {
        return $this->systemUpdates;
    }
    /**
     * @api
     */
    public function getLabelsRemoved(): int
    {
        return $this->labelsRemoved;
    }
}


