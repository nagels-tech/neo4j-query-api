<?php

namespace Neo4j\QueryAPI\Tests\Unit\objects;

use Neo4j\QueryAPI\Objects\Relationship;
use PHPUnit\Framework\TestCase;

final class RelationshipUnitTest extends TestCase
{
    private Relationship $relationship;
    #[\Override]
    protected function setUp(): void
    {
        $this->relationship = new Relationship('FRIENDS_WITH', ['since' => 2020]);
    }

    public function testGetTypeReturnsCorrectType(): void
    {
        $this->assertEquals('FRIENDS_WITH', $this->relationship->getType());
    }

    public function testGetPropertiesReturnsCorrectArray(): void
    {
        $this->assertEquals(['since' => 2020], $this->relationship->getProperties());
    }

    public function testEmptyPropertiesByDefault(): void
    {
        $relationship = new Relationship('KNOWS');
        $this->assertEquals([], $relationship->getProperties());
    }
}
