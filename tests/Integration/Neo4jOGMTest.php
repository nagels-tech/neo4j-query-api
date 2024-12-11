<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\OGM;
use PHPUnit\Framework\TestCase;

class Neo4jOGMTest extends TestCase
{
    private OGM $ogm;

    public function setUp(): void
    {
        $this->ogm = new OGM();
    }

    public function testInteger(): void
    {
        $this->assertEquals(30, $this->ogm->map([
            '$type' => 'Integer',
            '_value' => 30,
        ]));
    }

    public function testPoint(): void
    {
        $this->assertEquals(30, $this->ogm->map([
            '$type' => 'Point',
            '_value' => 'SRID=4326;POINT (1.2 3.4)',
        ]));
    }
}