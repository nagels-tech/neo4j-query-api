<?php

namespace Neo4j\QueryAPI\Objects;

final class ResultCounters
{
    public function __construct(
        public readonly bool $containsUpdates = false,
        public readonly int  $nodesCreated = 0,
        public readonly int  $nodesDeleted = 0,
        public readonly int  $propertiesSet = 0,
        public readonly int  $relationshipsCreated = 0,
        public readonly int  $relationshipsDeleted = 0,
        public readonly int  $labelsAdded = 0,
        public readonly int  $labelsRemoved = 0,
        public readonly int  $indexesAdded = 0,
        public readonly int  $indexesRemoved = 0,
        public readonly int  $constraintsAdded = 0,
        public readonly int $constraintsRemoved = 0,
        public readonly bool $containsSystemUpdates = false,
        public readonly int $systemUpdates = 0
    ) {
    }

}
