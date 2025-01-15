<?php

namespace Neo4j\QueryAPI\Objects;

class Bookmarks implements \Countable
{
    public function __construct(private array $bookmarks)
    {
    }
    /**
     * @api
     */
    public function addBookmarks(?Bookmarks $newBookmarks): void
    {
        if ($newBookmarks !== null) {
            $this->bookmarks = array_unique(array_merge($this->bookmarks, $newBookmarks->bookmarks));
        }
    }
    /**
     * @api
     */

    public function getBookmarks(): array
    {
        return $this->bookmarks;
    }
    /**
     * @api
     */
    public function count(): int
    {
        return count($this->bookmarks);
    }
}
