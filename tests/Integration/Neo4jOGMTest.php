<?php

namespace Neo4j\QueryAPI\Tests\Integration;


use Neo4j\QueryAPI\OGM;
use PHPUnit\Framework\TestCase;

/**
 *  @api
 */
class Neo4jOGMTest extends TestCase
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private OGM $ogm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ogm = new OGM();
    }


    public function testWithNode(): void
    {
        // Ensure the property $ogm is referenced
        $nodeData = [
            '$type' => 'Node',
            '_value' => [
                '_labels' => ['Person'],
                '_properties' => ['name' => ['_value' => 'Ayush']],
            ]
        ];

        $node = $this->ogm->map($nodeData);
        $this->assertEquals('Ayush', $node->getProperties()['name']['_value']);
    }

    // Example of using $ogm in another test
    public function testWithSimpleRelationship(): void
    {
        // Mapping the Relationship
        $relationshipData = [
            '$type' => 'Relationship',
            '_value' => [
                '_type' => 'FRIENDS',
                '_properties' => [],
            ]
        ];

        $relationship = $this->ogm->map($relationshipData);
        $this->assertEquals('FRIENDS', $relationship->getType());
    }

    // More tests...
    public function testWithPath(): void
    {
        // Flattened structure to match expected input
        $pathData = [
            '$type' => 'Path',
            '_value' => [
                [
                    '$type' => 'Node',
                    '_value' => ['name' => ['_value' => 'A']],
                ],
                [
                    '$type' => 'Relationship',
                    '_value' => ['_type' => 'FRIENDS', '_properties' => []],
                ],
                [
                    '$type' => 'Node',
                    '_value' => ['name' => ['_value' => 'B']],
                ],
            ]
        ];

        // Now this will work with map()
        $path = $this->ogm->map($pathData);

        // Continue with assertions
        $this->assertCount(2, $path->getNodes());
        $this->assertCount(1, $path->getRelationships());
        $this->assertEquals('A', $path->getNodes()[0]->getProperties()['name']['_value']);
        $this->assertEquals('B', $path->getNodes()[1]->getProperties()['name']['_value']);
    }

}
