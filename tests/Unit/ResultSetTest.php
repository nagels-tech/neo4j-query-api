<?php


namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ResultSetTest extends TestCase
{
    public function testEmptyKeysThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The keys array cannot be empty.');

        $mockOgm = $this->createMock(OGM::class);
        new ResultSet([], [], $mockOgm);
    }

    public function testValidResultSet(): void
    {

        $mockOgm = $this->createMock(OGM::class);
        $mockOgm->method('map')->willReturnCallback(fn($value) => $value['_value'] ?? null);

        // Create ResultSet
        $resultSet = new ResultSet(
            ['name', 'age', 'email'],
            [
                [
                    ['$type' => 'String', '_value' => 'Bob'],
                    ['$type' => 'Integer', '_value' => 20],
                    ['$type' => 'String', '_value' => 'bob@example.com'],
                ]
            ],
            $mockOgm
        );


        $rows = iterator_to_array($resultSet);
        $this->assertCount(1, $rows);


        $this->assertInstanceOf(ResultRow::class, $rows[0]);
        $this->assertEquals('Bob', $rows[0]->get('name'));
        $this->assertEquals(20, $rows[0]->get('age'));
        $this->assertEquals('bob@example.com', $rows[0]->get('email'));
    }

    public function testInvalidColumnAccess(): void
    {
        // Mock OGM
        $mockOgm = $this->createMock(OGM::class);
        $mockOgm->method('map')->willReturnCallback(fn($value) => $value['_value'] ?? null);

        // Create ResultSet
        $resultSet = new ResultSet(
            ['name', 'age', 'email'],
            [
                [
                    ['$type' => 'String', '_value' => 'Bob'],
                    ['$type' => 'Integer', '_value' => 20],
                    ['$type' => 'String', '_value' => 'bob@example.com'],
                ]
            ],
            $mockOgm
        );

        $rows = iterator_to_array($resultSet);


        $this->expectException(\OutOfBoundsException::class); //this exception for TICA
        $this->expectExceptionMessage('Column phone not found.');
        $rows[0]->get('phone');
    }


    public function testMultipleRows(): void
    {
        $mockOgm = $this->createMock(OGM::class);
        $mockOgm->method('map')->willReturnCallback(fn($value) => $value['_value'] ?? null);

        // Create ResultSet
        $resultSet = new ResultSet(
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
                    ['$type' => 'String', '_value' => 'SebastianBergmann@example.com']
                ]
            ],
            $mockOgm
        );
        $rows = iterator_to_array($resultSet);
        $this->assertCount(2, $rows);


        $this->assertInstanceOf(ResultRow::class, $rows[0]);
        $this->assertEquals('Bob', $rows[0]->get('name'));
        $this->assertEquals(20, $rows[0]->get('age'));
        $this->assertEquals('bob@example.com', $rows[0]->get('email'));


        $this->assertInstanceOf(ResultRow::class, $rows[1]);
        $this->assertEquals('Sebastian Bergmann', $rows[1]->get('name'));
        $this->assertEquals(41, $rows[1]->get('age'));
        $this->assertEquals('SebastianBergmann@example.com', $rows[1]->get('email'));
    }

}