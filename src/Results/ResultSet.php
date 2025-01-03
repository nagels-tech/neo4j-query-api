<?php
namespace Neo4j\QueryAPI\Results;

use IteratorAggregate;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\OGM;
use queryCounters;
use Traversable;

class ResultSet implements IteratorAggregate
{
    /**
     * @var list<ResultRow>
     */
    private array $rows = [];

    /**
     * @param OGM $ogm
     */
    public function __construct(private OGM $ogm, private ResultCounters $counters)
    {
    }

    /**
     *
     * @param array $keys
     * @param array $dataRows
     */
    public function initialize(array $dataRows): void
    {
        foreach ($dataRows as $dataRow) {
            $this->rows[] = new ResultRow($this->ogm->map($dataRow));
        }
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->rows);
    }
    public function getqueryCounters(): ?QueryCounters
    {
        return $this->counters;
    }
}
