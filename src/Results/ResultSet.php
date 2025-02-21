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
 * @api
 * @template TValue
 * @implements IteratorAggregate<int, ResultRow>
 */
class ResultSet implements IteratorAggregate, Countable
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(
        private readonly array $rows,
        private readonly ?ResultCounters $counters = null,
        private readonly Bookmarks $bookmarks,
        private readonly ?ProfiledQueryPlan $profiledQueryPlan,
        private readonly AccessMode $accessMode
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

    public function getProfiledQueryPlan(): ?ProfiledQueryPlan
    {
        return $this->profiledQueryPlan;
    }

    /**
     * @api
     */
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
