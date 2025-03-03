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
        public readonly Bookmarks $bookmarks,
        public readonly AccessMode $accessMode,
        public readonly ?ResultCounters $counters = null,
        public readonly ?ProfiledQueryPlan $profiledQueryPlan=null

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


    #[\Override]
    public function count(): int
    {
        return count($this->rows);
    }

}
