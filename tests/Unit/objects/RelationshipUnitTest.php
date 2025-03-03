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
        $relationship = $this->relationship;
        $this->assertEquals('FRIENDS_WITH', $relationship->type);
    }

    public function testGetPropertiesReturnsCorrectArray(): void
    {
        $relationship = $this->relationship;
        $this->assertEquals(['since' => 2020], $relationship->properties);
    }

    public function testEmptyPropertiesByDefault(): void
    {
        $relationship = new Relationship('KNOWS');
        $this->assertEquals([], $relationship->properties);
    }
}
