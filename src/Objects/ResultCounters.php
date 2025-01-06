<?php

namespace Neo4j\QueryAPI\Objects;

class ResultCounters
{
    public function __construct(
        private readonly bool $containsUpdates,
        private readonly int  $nodesCreated,
        private readonly int  $nodesDeleted,
        private readonly int  $propertiesSet,
        private readonly int  $relationshipsCreated,
        private readonly int  $relationshipsDeleted,
        private readonly int  $labelsAdded,
        private readonly int  $labelsRemoved,
        private readonly int  $indexesAdded,
        private readonly int  $indexesRemoved,
        private readonly int  $constraintsAdded,
        private readonly int $constraintsRemoved,
        private readonly bool $containsSystemUpdates,
        private readonly int $systemUpdates
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
    public function getBookmarks(): array
    {
        return $this->bookmarks;
    }

    public function addBookmark(string $bookmark): void
    {
        if (!in_array($bookmark, $this->bookmarks)) {
            $this->bookmarks[] = $bookmark;
        }
    }

//    public function clearBookmarks(): void
//    {
//        $this->bookmarks = [];
//    }

}


