<?php

namespace Neo4j\QueryAPI\Results;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Neo4j\QueryAPI\Enums\AccessMode;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Traversable;

/**
 * @template TValue
 * @implements IteratorAggregate<int, ResultRow>
 */
final class ResultSet implements IteratorAggregate, Countable
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(
        public readonly array $rows,
        public readonly ?ResultCounters $counters = null,
        public readonly Bookmarks $bookmarks,
        public readonly ?ProfiledQueryPlan $profiledQueryPlan,
        public readonly AccessMode $accessMode
    ) {
    }

    /**
     * @return Traversable<int, ResultRow>
     */
    #[\Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rows);
    }

    public function getQueryCounters(): ?ResultCounters
    {
        return $this->counters;
    }


    #[\Override]
    public function count(): int
    {
        return count($this->rows);
    }

    public function getBookmarks(): ?Bookmarks
    {
        return $this->bookmarks;
    }

    public function getAccessMode(): ?AccessMode
    {
        return $this->accessMode;
    }

    public function getData(): array
    {
        return $this->rows;
    }
}
