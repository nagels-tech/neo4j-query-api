<?php

namespace Neo4j\QueryAPI\Objects;

use JsonSerializable;

class Bookmarks implements \Countable, JsonSerializable
{
    public function __construct(private array $bookmarks)
    {
    }

    public function addBookmarks(?Bookmarks $newBookmarks): void
    {
        if ($newBookmarks !== null) {
            $this->bookmarks = array_unique(array_merge($this->bookmarks, $newBookmarks->bookmarks));
        }
    }


    public function getBookmarks(): array
    {
        return $this->bookmarks;
    }

    #[\Override]
    public function count(): int
    {
        return count($this->bookmarks);
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        return $this->bookmarks;
    }
}
