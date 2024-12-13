<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Objects\Path;
use Neo4j\QueryAPI\Objects\Person;
use Neo4j\QueryAPI\Objects\Point;
use Neo4j\QueryAPI\Objects\Point_3D;
use Neo4j\QueryAPI\Objects\Relationship;
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

    public function testFloat(): void
    {
        $this->assertEquals(1.75, $this->ogm->map([
            '$type' => 'float',
            '_value' => 1.75,
        ]));
    }

    public function testString(): void
    {
        $this->assertEquals('Alice', $this->ogm->map([
            '$type' => 'String',
            '_value' => 'Alice',
        ]));
    }

    public function testBoolean(): void
    {
        $this->assertEquals(true, $this->ogm->map([
            '$type' => 'Boolean',
            '_value' => true,
        ]));
    }

    public function testNull(): void
    {
        $this->assertEquals(null, $this->ogm->map([
            '$type' => 'Null',
            '_value' => null,
        ]));
    }

    public function testDate(): void
    {
        $this->assertEquals('2024-12-11T11:00:00Z', $this->ogm->map([
            '$type' => 'OffsetDateTime',
            '_value' => '2024-12-11T11:00:00Z',
        ]));
    }
    public function testDuration(): void
    {
        $this->assertEquals('P14DT16H12M', $this->ogm->map([
            '$type' => 'Duration',
            '_value' => 'P14DT16H12M',
        ]));
    }


    public function testWithWGS84_2DPoint(): void
    {
        $point = $this->ogm->map([
            '$type' => 'Point',
            '_value' => 'SRID=4326;POINT (1.2 3.4)',
        ]);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals(1.2, $point->getLongitude());
        $this->assertEquals(3.4, $point->getLatitude());
        $this->assertEquals(4326, $point->getSrid());
    }

    public function testWithWGS84_3DPoint(): void
    {
        // Simulate mapping the raw WKT data into the Point object
        $point = $this->ogm->map([
            '$type' => 'Point',
            '_value' => 'SRID=4979;POINT Z (12.34 56.78 100.5)',
        ]);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals(12.34, $point->getLongitude());
        $this->assertEquals(56.78, $point->getLatitude());
        $this->assertEquals(100.5, $point->getHeight());
        $this->assertEquals(4979, $point->getSrid());
    }
    public function testWithCartesian2DPoint(): void
    {
        $point = $this->ogm->map([
            '$type' => 'Point',
            '_value' => 'SRID=7203;POINT (10.5 20.7)',
        ]);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals(10.5, $point->getX());
        $this->assertEquals(20.7, $point->getY());
        $this->assertEquals(7203, $point->getSrid());
    }
    public function testWithCartesian3DPoint(): void
    {
        $point = $this->ogm->map([
            '$type' => 'Point',
            '_value' => 'SRID=9157;POINT Z (10.5 20.7 30.9)',
        ]);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals(10.5, $point->getX());
        $this->assertEquals(20.7, $point->getY());
        $this->assertEquals(30.9, $point->getZ());
        $this->assertEquals(9157, $point->getSrid());
    }


    public function testArray(): void
    {
        $arrayData = ['developer', 'python', 'neo4j'];

        $this->assertEquals($arrayData, $this->ogm->map([
            '$type' => 'Array',
            '_value' => $arrayData,
        ]));
    }
    public function testWithNode()
    {
        $data = [
            'data' => [
                'fields' => ['n'],
                'values' => [
                    [
                        [
                            '$type' => 'Node',
                            '_value' => [
                                '_labels' => ['Person'],
                                '_properties' => [
                                    'name' => ['_value' => 'Ayush'],
                                    'age' => ['_value' => 30],
                                    'location' => ['_value' => 'New York'],
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ];

        $nodeData = $data['data']['values'][0][0]['_value'];
        $node = new Person($nodeData['_properties']);

        $properties = $node->getProperties();

        $this->assertEquals('Ayush', $properties['name']['_value']);
        $this->assertEquals(30, $properties['age']['_value']);
        $this->assertEquals('New York', $properties['location']['_value']);
    }

    public function testWithSimpleRelationship()
    {
        $data = [
            'data' => [
                'fields' => ['a', 'b', 'r'],
                'values' => [
                    [
                        [
                            '$type' => 'Node',
                            '_value' => [
                                '_labels' => ['Person'],
                                '_properties' => ['name' => ['_value' => 'A']]
                            ]
                        ],
                        [
                            '$type' => 'Node',
                            '_value' => [
                                '_labels' => ['Person'],
                                '_properties' => ['name' => ['_value' => 'B']]
                            ]
                        ],
                        [
                            '$type' => 'Relationship',
                            '_value' => [
                                '_type' => 'FRIENDS',
                                '_properties' => []
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $aData = $data['data']['values'][0][0]['_value'];
        $bData = $data['data']['values'][0][1]['_value'];
        $relationshipData = $data['data']['values'][0][2]['_value'];

        $aNode = new Person($aData['_properties']);
        $bNode = new Person($bData['_properties']);
        $relationship = new Relationship($relationshipData['_type'], $relationshipData['_properties']);

        $this->assertEquals('A', $aNode->getProperties()['name']['_value']);
        $this->assertEquals('B', $bNode->getProperties()['name']['_value']);
        $this->assertEquals('FRIENDS', $relationship->getType());
    }

    public function testWithPath()
    {
        $data = [
            'data' => [
                'fields' => ['path'],
                'values' => [
                    [
                        [
                            '$type' => 'Path',
                            '_value' => [
                                [
                                    '$type' => 'Node',
                                    '_value' => [
                                        '_labels' => ['Person'],
                                        '_properties' => ['name' => ['_value' => 'A']],
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
                                        '_properties' => ['name' => ['_value' => 'B']],
                                    ],
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ];

        $pathData = $data['data']['values'][0][0]['_value'];
        $nodes = [];
        $relationships = [];

        foreach ($pathData as $item) {
            if ($item['$type'] === 'Node') {
                $nodes[] = new Person($item['_value']['_properties']);
            } elseif ($item['$type'] === 'Relationship') {
                $relationships[] = new Relationship($item['_value']['_type'], $item['_value']['_properties']);
            }
        }

        $path = new Path($nodes, $relationships);

        $this->assertCount(2, $path->getNodes());
        $this->assertCount(1, $path->getRelationships());
        $this->assertEquals('A', $path->getNodes()[0]->getProperties()['name']['_value']);
        $this->assertEquals('B', $path->getNodes()[1]->getProperties()['name']['_value']);
    }




}
