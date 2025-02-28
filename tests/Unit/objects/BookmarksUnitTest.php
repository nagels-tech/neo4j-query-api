<?php

namespace Neo4j\QueryAPI\Tests\Unit\objects;

use Neo4j\QueryAPI\Objects\Bookmarks;
use PHPUnit\Framework\TestCase;

final class BookmarksUnitTest extends TestCase
{
    private Bookmarks $bookmarks;
    #[\Override]
    protected function setUp(): void
    {
        $this->bookmarks = new Bookmarks(['bookmark1', 'bookmark2']);
    }

    public function testGetBookmarksReturnsCorrectArray(): void
    {
        $bookmarks = $this->bookmarks;
        $this->assertEquals(['bookmark1', 'bookmark2'], $bookmarks->bookmarks);
    }

    public function testAddBookmarksMergesUniqueValues(): void
    {
        $newBookmarks = new Bookmarks(['bookmark1', 'bookmark2', 'bookmark3']);
        $this->bookmarks->addBookmarks($newBookmarks);

        $bookmarks = $this->bookmarks;
        $this->assertEquals(['bookmark1', 'bookmark2', 'bookmark3'], array_values($bookmarks->bookmarks));
    }

    public function testAddBookmarksDoesNothingWhenNullIsPassed(): void
    {
        $this->bookmarks->addBookmarks(null);
        $bookmarks = $this->bookmarks;
        $this->assertEquals(['bookmark1', 'bookmark2'], $bookmarks->bookmarks);
    }

    public function testCountReturnsCorrectNumber(): void
    {
        $this->assertEquals(2, $this->bookmarks->count());
    }

    public function testJsonSerializeReturnsCorrectArray(): void
    {
        $this->assertEquals(['bookmark1', 'bookmark2'], $this->bookmarks->jsonSerialize());
    }
}
