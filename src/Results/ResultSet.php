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

}
