<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use GuzzleHttp\Exception\GuzzleException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIIntegrationTest extends TestCase
{
    private static ?Neo4jQueryAPI $api = null;

    public static function setUpBeforeClass(): void
    {
        self::$api = self::initializeApi();

        self::clearDatabase();
        self::setupConstraints();
        self::populateTestData(['bob1', 'alicy']);
        self::validateData();
    }

    private static function initializeApi(): Neo4jQueryAPI
    {
        return Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS'),
            getenv('NEO4J_USERNAME'),
            getenv('NEO4J_PASSWORD')
        );
    }

    private static function clearDatabase(): void
    {
        self::$api->run('MATCH (n) DETACH DELETE n', []);
    }

    private static function setupConstraints(): void
    {
        self::$api->run('CREATE CONSTRAINT IF NOT EXISTS FOR (p:Person) REQUIRE p.name IS UNIQUE', []);
    }

    private static function populateTestData(array $names): void
    {
        foreach ($names as $name) {
            self::$api->run('CREATE (:Person {name: $name})', ['name' => $name]);
        }
    }

    private static function validateData(): void
    {
        $response = self::$api->run('MATCH (p:Person) RETURN p.name AS name, p.email AS email, p.age AS age, p AS person', []);

        foreach ($response as $person) {
            echo $person->get('name');
            echo $person->get('email');
            echo $person->get('age');

        }
    }

    private function executeQuery(string $query, array $parameters): array
    {
        $response = self::$api->run($query, $parameters);

        if (!empty($response['errors'])) {
            throw new \RuntimeException('Query execution failed: ' . json_encode($response['errors']));
        }

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
        $decodedBinary = base64_decode('U29tZSByYW5kb20gYmluYXJ5IGRhdGE=');
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
            'testWithNumber' => [
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
            'testWithArray' => [
                'CREATE (n:Person {tags: $tags}) RETURN n.tags',
                ['tags' => ['developer', 'python', 'neo4j']],
                [
                    'data' => [
                        'fields' => ['n.tags'],
                        'values' => [
                            [
                                [
                                    '$type' => 'List',
                                    '_value' => [
                                        [],
                                        [],
                                        [],
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
            /*'testWithBinary' => [
                'CREATE (n:Person {binary:$binary}) RETURN n.binary',
                ['binary' => 'U29tZSByYW5kb20gYmluYXJ5IGRhdGE='],
                [
                    'data' => [
                        'fields' => ['n.binary'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Bytes',
                                    '_value' => 'U29tZSByYW5kb20gYmluYXJ5IGRhdGE=',
                                ],
                            ],
                        ],
                    ],
                ],
            ],*/
            'testWithPoint' => [
                'CREATE (n:Person {Point: point($Point)}) RETURN n.Point',
                [
                    'Point' => [
                        'longitude' => 1.2, // X-coordinate (longitude)
                        'latitude' => 3.4,  // Y-coordinate (latitude)
                        'crs' => 'wgs-84', // Geographic CRS (SRID=4326)
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
                'CREATE (a:Person {name: "A"}), (b:Person {name: "B"}), path = (a)-[r:FRIENDS]->(b) RETURN path',
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
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
