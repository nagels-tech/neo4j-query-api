<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Results\ResultRow;
use OutOfBoundsException;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;

/**
 *  @api
 */
class ResultRowTest extends TestCase
{
    public function testArrayAccessGet(): void
    {
        $row = new ResultRow([
            'name' => 'Bob',
            'age' => 20,
            'email' => 'bob@lovesalice.com'
        ]);

        $this->assertEquals('Bob', $row['name']);
        $this->assertEquals(20, $row['age']);
    }
    /** @psalm-suppress UnusedVariable */
    public function testArrayAccessInvalidKey(): void
    {
        $row = new ResultRow([
            'name' => 'Bob',
            'age' => 20
        ]);

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Column phone not found.');

        $value = $row['phone'];
    }

    public function testArrayAccessSetThrowsException(): void
    {
        $row = new ResultRow([
            'name' => 'Bob',
        ]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("You can't set the value of column age.");

        $row['age'] = 30;
    }

    public function testArrayAccessUnsetThrowsException(): void
    {
        $row = new ResultRow([
            'name' => 'Bob',
        ]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("You can't Unset name.");

        unset($row['name']);
    }
}
