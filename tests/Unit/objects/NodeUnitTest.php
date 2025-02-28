<?php

namespace Neo4j\QueryAPI\Tests\Unit\objects;

use Neo4j\QueryAPI\Objects\Node;
use PHPUnit\Framework\TestCase;

final class NodeUnitTest extends TestCase
{
    private Node $node;
    #[\Override]
    protected function setUp(): void
    {
        $this->node = new Node(['Label1', 'Label2'], ['key1' => 'value1', 'key2' => 42]);
    }

    public function testGetLabelsReturnsCorrectArray(): void
    {
        $node = $this->node;
        $this->assertEquals(['Label1', 'Label2'], $node->labels);
    }

    public function testGetPropertiesReturnsCorrectArray(): void
    {
        $node = $this->node;
        $this->assertEquals(['key1' => 'value1', 'key2' => 42], $node->properties);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $expected = [
            '_labels' => ['Label1', 'Label2'],
            '_properties' => ['key1' => 'value1', 'key2' => 42],
        ];

        $this->assertEquals($expected, $this->node->toArray());
    }
}
