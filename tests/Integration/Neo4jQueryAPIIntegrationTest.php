<?php
/*
namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    public function setUp(): void
    {
        $this->api = $this->initializeApi();

        $this->clearDatabase();
        $this->populateTestData(['bob1', 'alicy']);
    }

    private function initializeApi(): Neo4jQueryAPI
    {
        return Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS'),
            getenv('NEO4J_USERNAME'),
            getenv('NEO4J_PASSWORD')
        );
    }

    private function clearDatabase(): void
    {
        $this->api->run('MATCH (n) DETACH DELETE n', []);
    }

    private function populateTestData(array $names): void
    {
        foreach ($names as $name) {
            $this->api->run('CREATE (:Person {name: $name})', ['name' => $name]);
        }
    }

    private function executeQuery(string $query, array $parameters): array
    {
        $response = $this->api->run($query, $parameters);


        $response['data']['values'] = array_map(fn($row) => $row, $response['data']['values']);

        return $response;
    }

    #[DataProvider(methodName: 'queryProvider')]
    public function testRunSuccessWithParameters(
        string $query,
        array  $parameters,
        array  $expectedResults
    ): void
    {
        $results = $this->executeQuery($query, $parameters);
        $subsetResults = $this->createSubset($expectedResults, $results);

        $this->assertIsArray($results);
        $this->assertEquals($expectedResults, $subsetResults);
    }

    private function createSubset(array $expected, array $actual): array
    {
        $subset = [];

        foreach ($expected as $key => $value) {
            if (array_key_exists($key, $actual)) {
                $actualValue = $actual[$key];
                if (is_array($value) && is_array($actualValue)) {
                    $actualValue = $this->createSubset($value, $actualValue);
                }
                $subset[$key] = $actualValue;
            }
        }

        return $subset;
    }

    public static function queryProvider(): array
    {

        return [
            'testWithExactNames' => [
                'MATCH (n:Person) WHERE n.name IN $names RETURN n.name',
                ['names' => ['bob1', 'alicy']],
                [
                    'data' => [
                        'fields' => ['n.name'],
                        'values' => [
                            [
                                [
                                    '$type' => 'String',
                                    '_value' => 'bob1'
                                ]
                            ],
                            [
                                [
                                    '$type' => 'String',
                                    '_value' => 'alicy'
                                ]
                            ]
                        ],
                    ],
                ],
            ],
            'testWithSingleName' => [
                'MATCH (n:Person) WHERE n.name = $name RETURN n',
                ['name' => 'bob1'],
                [
                    'data' => [
                        'fields' => ['n'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Node',
                                    '_value' => [
                                        '_labels' => ['Person'],
                                        '_properties' => [
                                            'name' => [
                                                '$type' => 'String',
                                                '_value' => 'bob1',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ],
            ],
            'testWithNoMatchingNames' => [
                'MATCH (n:Person) WHERE n.name IN $names RETURN n.name',
                ['names' => ['charlie', 'david']],
                [
                    'data' => [
                        'fields' => ['n.name'],
                        'values' => [],
                    ],
                ],
            ],
            'testWithInteger' => [
                'CREATE (n:Person {age: $age}) RETURN n.age',
                ['age' => 30],
                [
                    'data' => [
                        'fields' => ['n.age'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Integer',
                                    '_value' => 30,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithFloat' => [
                'CREATE (n:Person {height: $height}) RETURN n.height',
                ['height' => 1.75],
                [
                    'data' => [
                        'fields' => ['n.height'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Float',
                                    '_value' => 1.75,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithNull' => [
                'CREATE (n:Person {middleName: $middleName}) RETURN n.middleName',
                ['middleName' => null],
                [
                    'data' => [
                        'fields' => ['n.middleName'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Null',
                                    '_value' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithBoolean' => [
                'CREATE (n:Person {isActive: $isActive}) RETURN n.isActive',
                ['isActive' => true],
                [
                    'data' => [
                        'fields' => ['n.isActive'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Boolean',
                                    '_value' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithString' => [
                'CREATE (n:Person {name: $name}) RETURN n.name',
                ['name' => 'Alice'],
                [
                    'data' => [
                        'fields' => ['n.name'],
                        'values' => [
                            [
                                [
                                    '$type' => 'String',
                                    '_value' => 'Alice',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithArray' => [

                'CREATE (n:Person {tags: $tags}) RETURN n.tags',
                [
                    'tags' => ['bob1', 'alicy'],
                ],
                [
                    'data' => [
                        'fields' => ['n.tags'],
                        'values' => [
                            [
                                [
                                    '$type' => 'List',
                                    '_value' => [
                                        [

                                            '$type' => 'String',
                                            '_value' => 'bob1'

                                        ],
                                        [
                                            '$type' => 'String',
                                            '_value' => 'alicy'

                                        ]
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithDate' => [
                'CREATE (n:Person {date: datetime($date)}) RETURN n.date',
                ['date' => "2024-12-11T11:00:00Z"],
                [
                    'data' => [
                        'fields' => ['n.date'],
                        'values' => [
                            [
                                [
                                    '$type' => 'OffsetDateTime',
                                    '_value' => '2024-12-11T11:00:00Z',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithDuration' => [
                'CREATE (n:Person {duration: duration($duration)}) RETURN n.duration',
                ['duration' => 'P14DT16H12M'],
                [
                    'data' => [
                        'fields' => ['n.duration'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Duration',
                                    '_value' => 'P14DT16H12M',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithWGS84_2DPoint' => [
                'CREATE (n:Person {Point: point($Point)}) RETURN n.Point',
                [
                    'Point' => [
                        'longitude' => 1.2,
                        'latitude' => 3.4,
                        'crs' => 'wgs-84',
                    ],
                ],
                [
                    'data' => [
                        'fields' => ['n.Point'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Point',
                                    '_value' => 'SRID=4326;POINT (1.2 3.4)',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithWGS84_3DPoint' => [
                'CREATE (n:Person {Point: point({longitude: $longitude, latitude: $latitude, height: $height, srid: $srid})}) RETURN n.Point',
                [
                    'longitude' => 12.34,
                    'latitude' => 56.78,
                    'height' => 100.5,
                    'srid' => 4979,
                ],
                [
                    'data' => [
                        'fields' => ['n.Point'],
                        'values' => [
                            0 => [
                                0 => [
                                    '$type' => 'Point',
                                    '_value' => 'SRID=4979;POINT Z (12.34 56.78 100.5)',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithCartesian2DPoint' => [
                'CREATE (n:Person {Point: point({x: $x, y: $y, srid: $srid})}) RETURN n.Point',
                [
                    'x' => 10.5,
                    'y' => 20.7,
                    'srid' => 7203,
                ],
                [
                    'data' => [
                        'fields' => ['n.Point'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Point',
                                    '_value' => 'SRID=7203;POINT (10.5 20.7)',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithCartesian3DPoint' => [
                'CREATE (n:Person {Point: point({x: $x, y: $y, z: $z, srid: $srid})}) RETURN n.Point',
                [
                    'x' => 10.5,
                    'y' => 20.7,
                    'z' => 30.9,
                    'srid' => 9157,
                ],
                [
                    'data' => [
                        'fields' => ['n.Point'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Point',
                                    '_value' => 'SRID=9157;POINT Z (10.5 20.7 30.9)',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithNode' => [
                'CREATE (n:Person {name: $name, age: $age, location: $location}) RETURN n',
                [
                    'name' => 'Ayush',
                    'age' => 30,
                    'location' => 'New York',
                ],
                [
                    'data' => [
                        'fields' => ['n'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Node',
                                    '_value' => [

                                        '_labels' => ['Person'],
                                        '_properties' => [
                                            'name' => [
                                                '$type' => 'String',
                                                '_value' => 'Ayush',
                                            ],
                                            'age' => [
                                                '$type' => 'Integer',
                                                '_value' => 30,
                                            ],
                                            'location' => [
                                                '$type' => 'String',
                                                '_value' => 'New York',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithSimpleRelationship' => [
                'CREATE (a:Person {name: "A"}), (b:Person {name: "B"}), (a)-[r:FRIENDS]->(b)RETURN a, b, r',
                [],
                [
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
                ],
            ],
            'testWithPath' => [
                'CREATE (a:Person {name: "A"}), (b:Person {name: "B"}), (a) - [r:FRIENDS] -> (b) WITH a, b, r MATCH path = (a) - [r] -> (b) RETURN path',
                [],
                [
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
                                            ]
                                        ],
                                        [
                                            '$type' => 'Relationship',
                                            '_value' => [
                                                '_type' => 'FRIENDS',
                                                '_properties' => [],
                                            ]
                                        ],
                                        [
                                            '$type' => 'Node',
                                            '_value' => [
                                                '_labels' => ['Person'],
                                                '_properties' => ['name' => ['_value' => 'B']],
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'testWithMap' => [
                'RETURN {hello: "hello"} AS map',
                [],
                [
                    'data' => [
                        'fields' => ['map'],
                        'values' => [ // rows
                            [ // first row
                                [ // first element in the first row
                                    '$type' => 'Map',
                                    '_value' => [
                                        'hello' => [
                                            '$type' => 'String',
                                            '_value' => 'hello',
                                        ]
                                    ],


                                ],
                            ]
                        ],
                    ],
                ],
            ],


        ];
    }
}*/
