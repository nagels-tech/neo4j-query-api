<?php

namespace Neo4j\QueryAPI\Tests\Unit\objects;

use Neo4j\QueryAPI\Objects\Point;
use PHPUnit\Framework\TestCase;

class PointUnitTest extends TestCase
{
    private Point $point;
    #[\Override]
    protected function setUp(): void
    {
        $this->point = new Point(1.5, 2.5, 3.5, 4326);
    }

    public function testGetXReturnsCorrectValue(): void
    {
        $point = $this->point;
        $this->assertEquals(1.5, $point->x);
    }

    public function testGetYReturnsCorrectValue(): void
    {
        $point = $this->point;
        $this->assertEquals(2.5, $point->y);
    }

    public function testGetZReturnsCorrectValue(): void
    {
        $point = $this->point;
        $this->assertEquals(3.5, $point->z);
    }

    public function testGetSridReturnsCorrectValue(): void
    {
        $point = $this->point;
        $this->assertEquals(4326, $point->srid);
    }

    public function testToStringReturnsCorrectFormat(): void
    {
        $this->assertEquals('SRID=4326;POINT (1.5 2.5)', (string) $this->point);
    }
}
