<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Enums\AccessMode;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Point;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Tests\CreatesQueryAPI;
use PHPUnit\Framework\TestCase;

final class DataTypesIntegrationTest extends TestCase
{
    use CreatesQueryAPI;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createQueryAPI();
    }

    public function testWithExactNames(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.name' => 'bob1']),
                new ResultRow(['n.name' => 'alicy']),
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(),
            null
        );

        $results = $this->api->run('MATCH (n:Person) WHERE n.name IN $names RETURN n.name', [
            'names' => ['bob1', 'alicy']
        ]);

        $this->assertEquals($expected->counters, $results->counters);
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }
    public function testWithSingleName(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.name' => 'bob1']),
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(),
            null
        );

        $results = $this->api->run('MATCH (n:Person) WHERE n.name = $name RETURN n.name LIMIT 1', [
            'name' => 'bob1'
        ]);

        $this->assertEquals($expected->counters, $results->counters);
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }


    public function testWithInteger(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.age' => 30]),
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            null
        );

        $results = $this->api->run('CREATE (n:Person {age: $age}) RETURN n.age', [
            'age' => 30
        ]);

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }


    public function testWithFloat(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.height' => 1.75]),
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            null
        );

        $results = $this->api->run('CREATE (n:Person {height: $height}) RETURN n.height', [
            'height' => 1.75
        ]);

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }


    public function testWithNull(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.middleName' => null]),
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 0,
                labelsAdded: 1,
            ),
            null
        );

        $results = $this->api->run('CREATE (n:Person {middleName: $middleName}) RETURN n.middleName', [
            'middleName' => null
        ]);

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }


    public function testWithBoolean(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.isActive' => true]),
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            null
        );

        $results = $this->api->run('CREATE (n:Person {isActive: $isActive}) RETURN n.isActive', [
            'isActive' => true
        ]);

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }


    public function testWithString(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.name' => 'Alice']),
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            null
        );

        $results = $this->api->run('CREATE (n:Person {name: $name}) RETURN n.name', [
            'name' => 'Alice'
        ]);

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }


    public function testWithArray(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.name' => 'bob1']),
                new ResultRow(['n.name' => 'alicy'])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: false,
                nodesCreated: 0,
                propertiesSet: 0,
                labelsAdded: 0,
            ),
            null
        );

        $results = $this->api->run(
            'MATCH (n:Person) WHERE n.name IN $names RETURN n.name',
            ['names' => ['bob1', 'alicy']]
        );

        $this->assertEquals($expected->counters, $results->counters);
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }


    public function testWithDate(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.date' => '2024-12-11T11:00:00Z'])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1
            ),
            null
        );

        $results = $this->api->run(
            'CREATE (n:Person {date: datetime($date)}) RETURN n.date',
            ['date' => "2024-12-11T11:00:00Z"]
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

    public function testWithDuration(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.duration' => 'P14DT16H12M'])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1
            ),
            null
        );

        $results = $this->api->run(
            'CREATE (n:Person {duration: duration($duration)}) RETURN n.duration',
            ['duration' => 'P14DT16H12M']
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

    public function testWithWGS84_2DPoint(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.Point' => 'SRID=4326;POINT (1.2 3.4)'])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1
            ),
            null
        );

        $results = $this->api->run(
            'CREATE (n:Person {Point: point($Point)}) RETURN n.Point',
            [
                'Point' => [
                    'longitude' => 1.2,
                    'latitude' => 3.4,
                    'crs' => 'wgs-84'
                ]
            ]
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

    public function testWithWGS84_3DPoint(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.Point' => new Point(1.2, 3.4, 4.2, 4979)])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1
            ),
            null
        );

        $results = $this->api->run(
            'CREATE (n:Person {Point: point({longitude: $longitude, latitude: $latitude, height: $height, srid: $srid})}) RETURN n.Point',
            [
                'longitude' => 1.2,
                'latitude' => 3.4,
                'height' => 4.2,
                'srid' => 4979
            ]
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

    public function testWithCartesian2DPoint(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.Point' => new Point(10.5, 20.7, null, 7203)])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1
            ),
            null
        );

        $results = $this->api->run(
            'CREATE (n:Person {Point: point({x: $x, y: $y, srid: $srid})}) RETURN n.Point',
            [
                'x' => 10.5,
                'y' => 20.7,
                'srid' => 7203
            ]
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

    public function testWithCartesian3DPoint(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.Point' => new Point(10.5, 20.7, 30.9, 9157)])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1
            ),
            null
        );

        $results = $this->api->run(
            'CREATE (n:Person {Point: point({x: $x, y: $y, z: $z, srid: $srid})}) RETURN n.Point',
            [
                'x' => 10.5,
                'y' => 20.7,
                'z' => 30.9,
                'srid' => 9157
            ]
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

    public function testWithNode(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow([
                    'node' => [
                        'properties' => [
                            'name' => 'Ayush',
                            'location' => 'New York',
                            'age' => '30'
                        ],
                        'labels' => [
                            0 => 'Person'
                        ]
                    ]
                ])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 3,
                labelsAdded: 1
            ),
            null
        );

        $results = $this->api->run(
            'CREATE (n:Person {name: $name, age: $age, location: $location}) RETURN {labels: labels(n), properties: properties(n)} AS node',
            [
                'name' => 'Ayush',
                'age' => 30,
                'location' => 'New York'
            ]
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

    public function testWithPath(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow([
                    'node1' => [
                        'labels' => ['Person'],
                        'properties' => [
                            'name' => 'A'
                        ]
                    ],
                    'node2' => [
                        'labels' => ['Person'],
                        'properties' => [
                            'name' => 'B'
                        ]
                    ],
                    'relationshipTypes' => ['FRIENDS']
                ])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 2,
                propertiesSet: 2,
                relationshipsCreated: 1,
                labelsAdded: 2
            ),
            null
        );

        $results = $this->api->run(
            'CREATE (a:Person {name: $name1}), (b:Person {name: $name2}),
         (a)-[r:FRIENDS]->(b)
         RETURN {labels: labels(a), properties: properties(a)} AS node1,
                {labels: labels(b), properties: properties(b)} AS node2,
                collect(type(r)) AS relationshipTypes',
            [
                'name1' => 'A',
                'name2' => 'B'
            ]
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

    public function testWithMap(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['map' => ['hello' => 'hello']])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: false,
                nodesCreated: 0,
                propertiesSet: 0,
                labelsAdded: 0
            ),
            null
        );

        $results = $this->api->run(
            'RETURN {hello: "hello"} AS map',
            []
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

    public function testWithRelationship(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow([
                    'node1' => [
                        'labels' => ['Person'],
                        'properties' => [
                            'name' => 'Ayush',
                            'age' => 30,
                            'location' => 'New York'
                        ]
                    ],
                    'node2' => [
                        'labels' => ['Person'],
                        'properties' => [
                            'name' => 'John',
                            'age' => 25,
                            'location' => 'Los Angeles'
                        ]
                    ],
                    'relationshipType' => 'FRIEND_OF'
                ])
            ],
            new Bookmarks([]),
            AccessMode::WRITE,
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 2,
                propertiesSet: 6,
                relationshipsCreated: 1,
                labelsAdded: 2
            ),
            null
        );

        $results = $this->api->run(
            'CREATE (p1:Person {name: $name1, age: $age1, location: $location1}),
         (p2:Person {name: $name2, age: $age2, location: $location2}),
         (p1)-[r:FRIEND_OF]->(p2)
         RETURN {labels: labels(p1), properties: properties(p1)} AS node1,
                {labels: labels(p2), properties: properties(p2)} AS node2,
                type(r) AS relationshipType',
            [
                'name1' => 'Ayush',
                'age1' => 30,
                'location1' => 'New York',
                'name2' => 'John',
                'age2' => 25,
                'location2' => 'Los Angeles'
            ]
        );

        $this->assertEquals($expected->counters, $results->counters);
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $bookmarks = $results->bookmarks;
        $this->assertCount(1, $bookmarks);
    }

}
