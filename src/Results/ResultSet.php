<?php
//
namespace Neo4j\QueryAPI\Results;

use InvalidArgumentException;
use IteratorAggregate;
use Neo4j\QueryAPI\OGM;
use Traversable;

class ResultSet implements IteratorAggregate
{
    private array $rows;

    public function __construct(private array $keys, private array $resultRows, private OGM $ogm)
    {
        if (empty($this->keys)) {
            throw new InvalidArgumentException('The keys array cannot be empty.');
        }

        $this->rows = array_map(function ($resultRow) {
            $data = [];
            foreach ($this->keys as $index => $key) {
                $fieldData = $resultRow[$index] ?? null;
                $data[$key] = $this->ogm->map($fieldData);
            }
            return new ResultRow($data);
        }, $this->resultRows);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->rows);
    }
}
