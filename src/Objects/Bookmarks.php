<?php

namespace Neo4j\QueryAPI\Objects;

/**
 * @api
 */
class Bookmarks implements \Countable
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

    public function count(): int
    {
        return count($this->bookmarks);
    }
}
