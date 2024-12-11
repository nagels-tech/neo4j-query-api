<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Objects\Person;
use Neo4j\QueryAPI\Objects\Point;
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


    public function testPoint(): void
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
        // Simulate the result from the Neo4j database query
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

        $this->assertEquals('Ayush', $properties['name']['_value']);  // Ensure 'name' is a string
        $this->assertEquals(30, $properties['age']['_value']);        // Ensure 'age' is an integer
        $this->assertEquals('New York', $properties['location']['_value']); // Ensure 'location' is a string
    }

    public function testWithSimpleRelationship()
    {
        // Simulate the result from the Neo4j database query
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

        // Parse the response and create the nodes and relationship
        $aData = $data['data']['values'][0][0]['_value'];
        $bData = $data['data']['values'][0][1]['_value'];
        $relationshipData = $data['data']['values'][0][2]['_value'];

        $aNode = new Person($aData['_properties']);
        $bNode = new Person($bData['_properties']);
        $relationship = new Relationship($relationshipData['_type'], $relationshipData['_properties']);

        // Assertions
        $this->assertEquals('A', $aNode->getProperties()['name']['_value']);
        $this->assertEquals('B', $bNode->getProperties()['name']['_value']);
        $this->assertEquals('FRIENDS', $relationship->getType());
    }

}