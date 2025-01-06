<?php


use PHPUnit\Framework\TestCase;

class queryCountersTest extends TestCase
{
    public function testGetExistingCounter(): void
    {
        $counters = new \Neo4j\QueryAPI\Objects\ResultCounters([
            'nodes_created' => 1,
            'relationships_created' => 2,
            'properties_set' => 3,
        ]);

        $this->assertEquals(1, $counters->get('nodes_created'));
        $this->assertEquals(2, $counters->get('relationships_created'));
        $this->assertEquals(3, $counters->get('properties_set'));
    }

    public function testGetNonExistingCounterReturnsZero(): void
    {
        $counters = new queryCounters([
            'nodes_created' => 1,
        ]);

        $this->assertEquals(0, $counters->get('labels_added'));
        $this->assertEquals(0, $counters->get('relationships_deleted'));
    }

    public function testToArray(): void
    {
        $inputCounters = [
            'nodes_created' => 1,
            'relationships_created' => 2,
            'properties_set' => 3,
        ];

        $counters = new queryCounters($inputCounters);

        $this->assertEquals($inputCounters, $counters->toArray());
    }

    public function testEmptyCountersArray(): void
    {
        $counters = new queryCounters([]);

        $this->assertEquals(0, $counters->get('nodes_created'));
        $this->assertEquals(0, $counters->get('relationships_created'));
        $this->assertEmpty($counters->toArray());
    }
}
