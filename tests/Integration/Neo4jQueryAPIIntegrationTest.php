<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use GuzzleHttp\Exception\GuzzleException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIIntegrationTest extends TestCase
{
    private static ?Neo4jQueryAPI $api = null;

    /**
     * Establish the connection and prepare the database.
     * @throws GuzzleException
     */
    public static function setUpBeforeClass(): void
    {
        self::$api = self::initializeApi();

        self::clearDatabase();
        self::setupConstraints();
        self::populateTestData(['bob1', 'alicy']);
        self::validateData();
    }

    /**
     * Initializes the Neo4j API connection.
     * @throws GuzzleException
     */
    private static function initializeApi(): Neo4jQueryAPI
    {
        return Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS'),
            getenv('NEO4J_USERNAME'),
            getenv('NEO4J_PASSWORD')
        );
    }

    /**
     * Clears all data from the database.
     * @throws GuzzleException
     */
    private static function clearDatabase(): void
    {
        self::$api->run('MATCH (n) DETACH DELETE n', []);
    }

    /**
     * Creates required database constraints.
     * @throws GuzzleException
     */
    private static function setupConstraints(): void
    {
        self::$api->run('CREATE CONSTRAINT IF NOT EXISTS FOR (p:Person) REQUIRE p.name IS UNIQUE', []);
    }

    /**
     * Inserts test data into the database.
     * @param array $names
     * @throws GuzzleException
     */
    private static function populateTestData(array $names): void
    {
        foreach ($names as $name) {
            self::$api->run('CREATE (:Person {name: $name})', ['name' => $name]);
        }
    }

    /**
     * Validates that test data has been inserted correctly.
     * @throws GuzzleException
     */
    private static function validateData(): void
    {
        $response = self::$api->run('MATCH (p:Person) RETURN p.name', []);
        // Remove the print_r statement in production
        // print_r($response);
    }

    /**
     * Executes a query and normalizes the response.
     * @throws GuzzleException
     */
    private function executeQuery(string $query, array $parameters): array
    {
        $response = self::$api->run($query, $parameters);

        if (!empty($response['errors'])) {
            throw new \RuntimeException('Query execution failed: ' . json_encode($response['errors']));
        }

        // Normalize the response format
        $response['data']['values'] = array_map(fn($row) => $row, $response['data']['values']);

        return $response;
    }

    /**
     * Test querying the database with parameters.
     * @throws GuzzleException
     */
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

    /**
     * Cleans the actual result so only the keys in the expected results are mapped to the returned result.
     *
     * @param array $expected
     * @param array $actual
     * @return array
     */
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

    /**
     * Provides test cases for query tests.
     */
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
            // Test with number data type
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
            // Test with null data type
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
                ['isActive' => true],  // You can change this to `false` if needed
                [
                    'data' => [
                        'fields' => ['n.isActive'],
                        'values' => [
                            [
                                [
                                    '$type' => 'Boolean',
                                    '_value' => true,  // This can be `false` if you want to test the false value
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'testWithArray' => [
                'CREATE (n:Person {tags: $tags}) RETURN n.tags',
                ['tags' => ['developer', 'python', 'neo4j']],  // An array of tags
                [
                    'data' => [
                        'fields' => ['n.tags'],
                        'values' => [
                            [
                                [
                                    '$type' => 'List',  // Indicating that it's an array (list)
                                    '_value' => [
                                        [],  // First tag as an array
                                        [],     // Second tag as an array
                                        [],      // Third tag as an array
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
