<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\OGM;
use PHPUnit\Framework\TestCase;

final class Neo4jOGMTest extends TestCase
{
    private OGM $ogm;

    #[\Override]
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
    public function testWithPath(): void
    {
        $pathData = [
            '$type' => 'Path',
            '_value' => [
                [
                    '$type' => 'Node',
                    '_value' => [
                        '_labels' => ['Person'],
                        '_properties' => [
                            'name' => ['_value' => 'A'],
                        ],
                    ],
                ],
                [
                    '$type' => 'Relationship',
                    '_value' => [
                        '_type' => 'FRIENDS',
                        '_properties' => [],
                    ],
                ],
                [
                    '$type' => 'Node',
                    '_value' => [
                        '_labels' => ['Person'],
                        '_properties' => [
                            'name' => ['_value' => 'B'],
                        ],
                    ],
                ],
            ]
        ];

        $path = $this->ogm->map($pathData);

        // Assertions
        $this->assertCount(2, $path->getNodes());
        $this->assertCount(1, $path->getRelationships());
        $this->assertEquals('A', $path->getNodes()[0]->getProperties()['name']['_value']);
        $this->assertEquals('B', $path->getNodes()[1]->getProperties()['name']['_value']);
    }

}
