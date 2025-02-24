<?php

namespace Neo4j\QueryAPI\Tests\Unit\objects;

use Neo4j\QueryAPI\Objects\Point;
use PHPUnit\Framework\TestCase;

final class PointUnitTest extends TestCase
{
    private Point $point;
    #[\Override]
    protected function setUp(): void
    {
        $this->point = new Point(1.5, 2.5, 3.5, 4326);
    }

    public function testGetXReturnsCorrectValue(): void
    {
        $this->assertEquals(1.5, $this->point->getX());
    }

    public function testGetYReturnsCorrectValue(): void
    {
        $this->assertEquals(2.5, $this->point->getY());
    }

    public function testGetZReturnsCorrectValue(): void
    {
        $this->assertEquals(3.5, $this->point->getZ());
    }

    public function testGetSridReturnsCorrectValue(): void
    {
        $this->assertEquals(4326, $this->point->getSrid());
    }

    public function testToStringReturnsCorrectFormat(): void
    {
        $this->assertEquals('SRID=4326;POINT (1.5 2.5)', (string) $this->point);
    }
}
