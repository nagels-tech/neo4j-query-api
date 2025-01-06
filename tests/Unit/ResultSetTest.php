<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use InvalidArgumentException;
use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class ResultSetTest extends TestCase
{
    /**
     * Test that an empty keys array throws an exception.
     */

    public function testEmptyKeysThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The keys array cannot be empty.');

        $mockOgm = $this->createMock(OGM::class);
        $resultSet = new ResultSet($mockOgm);

        // Call initialize with empty keys
        $resultSet->initialize([], [
            [
                ['$type' => 'String', '_value' => 'Bob'],
            ],
        ]);
    }

    /**
     * Test that a valid ResultSet can be created and accessed.
     */
    public function testValidResultSet(): void
    {
        $mockOgm = $this->createMock(OGM::class);
        $mockOgm->method('map')->willReturnCallback(fn($value) => $value['_value'] ?? null);

        $resultSet = new ResultSet($mockOgm);
        $resultSet->initialize(
            ['name', 'age', 'email'],
            [
                [
                    ['$type' => 'String', '_value' => 'Bob'],
                    ['$type' => 'Integer', '_value' => 20],
                    ['$type' => 'String', '_value' => 'bob@example.com'],
                ],
            ],
            $mockOgm
        );

        $rows = iterator_to_array($resultSet);

        // Assertions
        $this->assertCount(1, $rows);
        $this->assertInstanceOf(ResultRow::class, $rows[0]);
        $this->assertEquals('Bob', $rows[0]->get('name'));
        $this->assertEquals(20, $rows[0]->get('age'));
        $this->assertEquals('bob@example.com', $rows[0]->get('email'));
    }

    /**
     * Test accessing an invalid column throws an OutOfBoundsException.
     */
    public function testInvalidColumnAccess(): void
    {
        $mockOgm = $this->createMock(OGM::class);
        $mockOgm->method('map')->willReturnCallback(fn($value) => $value['_value'] ?? null);

        $resultSet = new ResultSet($mockOgm);
        $resultSet->initialize(
            ['name', 'age', 'email'],
            [
                [
                    ['$type' => 'String', '_value' => 'Bob'],
                    ['$type' => 'Integer', '_value' => 20],
                    ['$type' => 'String', '_value' => 'bob@example.com'],
                ],
            ],
            $mockOgm
        );

        $rows = iterator_to_array($resultSet);

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Column phone not found.');

        $rows[0]->get('phone');
    }

    /**
     * Test that multiple rows are correctly handled in the ResultSet.
     */
    public function testMultipleRows(): void
    {
        $mockOgm = $this->createMock(OGM::class);
        $mockOgm->method('map')->willReturnCallback(fn($value) => $value['_value'] ?? null);

        $resultSet = new ResultSet($mockOgm);
        $resultSet->initialize(
            ['name', 'age', 'email'],
            [
                [
                    ['$type' => 'String', '_value' => 'Bob'],
                    ['$type' => 'Integer', '_value' => 20],
                    ['$type' => 'String', '_value' => 'bob@example.com'],
                ],
                [
                    ['$type' => 'String', '_value' => 'Sebastian Bergmann'],
                    ['$type' => 'Integer', '_value' => 41],
                    ['$type' => 'String', '_value' => 'SebastianBergmann@example.com'],
                ],
            ],
            $mockOgm
        );

        $rows = iterator_to_array($resultSet);

        // Assertions for the first row
        $this->assertCount(2, $rows);
        $this->assertInstanceOf(ResultRow::class, $rows[0]);
        $this->assertEquals('Bob', $rows[0]->get('name'));
        $this->assertEquals(20, $rows[0]->get('age'));
        $this->assertEquals('bob@example.com', $rows[0]->get('email'));

        // Assertions for the second row
        $this->assertInstanceOf(ResultRow::class, $rows[1]);
        $this->assertEquals('Sebastian Bergmann', $rows[1]->get('name'));
        $this->assertEquals(41, $rows[1]->get('age'));
        $this->assertEquals('SebastianBergmann@example.com', $rows[1]->get('email'));
    }
}
