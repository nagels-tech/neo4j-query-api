<?php

namespace Neo4j\QueryAPI\Tests\Unit\Results;

use Neo4j\QueryAPI\Results\ResultRow;
use PHPUnit\Framework\TestCase;
use OutOfBoundsException;
use BadMethodCallException;

final class ResultRowUnitTest extends TestCase
{
    public function testOffsetGetReturnsValue(): void
    {
        $data = ['name' => 'Alice', 'age' => 25];
        $row = new ResultRow($data);

        $this->assertEquals('Alice', $row->offsetGet('name'));
        $this->assertEquals(25, $row->offsetGet('age'));
    }

    public function testOffsetGetThrowsExceptionForInvalidKey(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Column invalid_key not found.");

        $data = ['name' => 'Alice'];
        $row = new ResultRow($data);

        $row->offsetGet('invalid_key');
    }

    public function testOffsetExists(): void
    {
        $data = ['name' => 'Alice', 'age' => 25];
        $row = new ResultRow($data);

        $this->assertTrue($row->offsetExists('name'));
        $this->assertTrue($row->offsetExists('age'));
        $this->assertFalse($row->offsetExists('invalid_key'));
    }

    public function testOffsetSetThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("You can't set the value of column new_key.");

        $data = ['name' => 'Alice'];
        $row = new ResultRow($data);

        $row->offsetSet('new_key', 'value');
    }

    public function testOffsetUnsetThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("You can't Unset name.");

        $data = ['name' => 'Alice'];
        $row = new ResultRow($data);

        $row->offsetUnset('name');
    }

    public function testGetReturnsValue(): void
    {
        $data = ['name' => 'Alice', 'age' => 25];
        $row = new ResultRow($data);

        $this->assertEquals('Alice', $row->get('name'));
        $this->assertEquals(25, $row->get('age'));
    }

    public function testCount(): void
    {
        $data = ['name' => 'Alice', 'age' => 25];
        $row = new ResultRow($data);

        $this->assertCount(2, $row);
    }

    public function testIterator(): void
    {
        $data = ['name' => 'Alice', 'age' => 25];
        $row = new ResultRow($data);

        $values = [];
        foreach ($row as $key => $value) {
            $values[$key] = $value;
        }

        $this->assertEquals($data, $values);
    }
}
