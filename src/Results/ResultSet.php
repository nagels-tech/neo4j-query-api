<?php

namespace Neo4j\QueryAPI\Results;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Traversable;
use Neo4j\QueryAPI\Enums\AccessMode;


/**
 * @api
 * @template TKey of array-key
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
class ResultSet implements IteratorAggregate, Countable
{
    /**
     * @param list<ResultRow> $rows
     */
    public function __construct(
        private readonly array     $rows,
        private ?ResultCounters     $counters = null,
        private Bookmarks          $bookmarks,
        private ?ProfiledQueryPlan $profiledQueryPlan,
        private AccessMode          $accessMode
    )
    {


    }

    /**
     * @return Traversable<int, ResultRow>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rows);
    }

    /**
     * @api
     */
    public function getQueryCounters(): ?ResultCounters
    {
        return $this->counters;
    }

    /**
     * @api
     */

    public function getProfiledQueryPlan(): ?ProfiledQueryPlan
    {
        return $this->profiledQueryPlan;
    }

    /**
     * @api
     */
    public function count(): int
    {
        return count($this->rows);
    }

    /**
     * @api
     */
    public function getBookmarks(): ?Bookmarks
    {
        return $this->bookmarks;
    }

    /**
     * @api
     */

    public function getAccessMode(): ?AccessMode
    {
        return $this->accessMode;
    }
    public function getData(): array
    {
        return $this->rows;
    }


//    public function getImpersonatedUser(): ?ImpersonatedUser
//    {
//
//    }




}
