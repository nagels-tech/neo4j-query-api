<?php

namespace Neo4j\QueryAPI\Tests\Unit\objects;

use Neo4j\QueryAPI\Objects\Path;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Relationship;
use PHPUnit\Framework\TestCase;

final class PathUnitTest extends TestCase
{
    private Path $path;
    private array $nodes;
    private array $relationships;
    #[\Override]
    protected function setUp(): void
    {
        $this->nodes = [
            new Node(['Person'], ['name' => 'Alice']),
            new Node(['Person'], ['name' => 'Bob'])
        ];

        $this->relationships = [
            new Relationship('KNOWS', ['since' => 2020])
        ];

        $this->path = new Path($this->nodes, $this->relationships);
    }

    public function testGetNodesReturnsCorrectArray(): void
    {
        $this->assertEquals($this->nodes, $this->path->getNodes());
    }

    public function testGetRelationshipsReturnsCorrectArray(): void
    {
        $this->assertEquals($this->relationships, $this->path->getRelationships());
    }
}
