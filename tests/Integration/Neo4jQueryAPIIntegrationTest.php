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
     * @throws GuzzleException
     */
    public static function setUpBeforeClass(): void
    {
        self::$api = Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS'),
            getenv('NEO4J_USERNAME'),
            getenv('NEO4J_PASSWORD')
        );

        // Clear the database
        self::clearDatabase(self::$api);

        // Create necessary constraints
        self::createConstraints(self::$api);

        // Insert test data
        self::populateFixtures(self::$api, ['bob1', 'alicy']);

        // Validate fixtures
        self::validateFixtures(self::$api);
    }

    /**
     * @throws GuzzleException
     */
    private static function clearDatabase(Neo4jQueryAPI $api): void
    {
        $api->run('MATCH (n) DETACH DELETE n', []);
    }

    /**
     * @throws GuzzleException
     */
    private static function createConstraints(Neo4jQueryAPI $api): void
    {
        $api->run('CREATE CONSTRAINT IF NOT EXISTS FOR (p:Person1) REQUIRE p.name IS UNIQUE', []);
    }

    /**
     * @throws GuzzleException
     */
    private static function populateFixtures(Neo4jQueryAPI $api, array $names): void
    {
        foreach ($names as $name) {
            $api->run('CREATE (:Person {name: $name})', ['name' => $name]);
        }
    }

    /**
     * @throws GuzzleException
     */
    private static function validateFixtures(Neo4jQueryAPI $api): void
    {
        $results = $api->run('MATCH (p:Person) RETURN p.name', []);
        print_r($results);
    }

    /**
     * @throws GuzzleException
     */
    #[DataProvider(methodName: 'queryProvider')]
    public function testRunSuccessWithParameters(
        string $address,
        string $username,
        string $password,
        string $query,
        array  $parameters,
        array  $expectedResults
    ): void
    {
        $results = $this->executeQuery($query, $parameters);

        // Normalize the results before assertion
        $results = $this->normalizeResults($results);

        // Remove bookmarks if present
        unset($results['bookmarks']);

        $this->assertIsArray($results);
        $this->assertEquals($expectedResults, $results);
    }

    /**
     * Executes the query using the Neo4j API.
     *
     * @throws GuzzleException
     */
    private function executeQuery(string $query, array $parameters): array
    {
        // Check if the API connection is initialized
        if (self::$api === null) {
            throw new \Exception('API connection is not initialized.');
        }

        // Execute the query
        $response = self::$api->run($query, $parameters);

        // Check if the response contains any error
        if (isset($response['errors']) && !empty($response['errors'])) {
            throw new \Exception('Error executing query: ' . json_encode($response['errors']));
        }

        return $response;
    }

    /**
     * Normalize the Neo4j results to match the expected format.
     */
    private function normalizeResults(array $results): array
    {
        // Check if the results contain 'fields' and 'values'
        if (isset($results['data']) && is_array($results['data'])) {
            // Normalize data into 'fields' and 'values' format
            $fields = [];
            $values = [];

            foreach ($results['data'] as $row) {
                if (isset($row['row']) && is_array($row['row'])) {
                    // Ensure each row's field-value pairs are added to fields and values
                    foreach ($row['row'] as $key => $value) {
                        $fields[] = $key; // Add field name (e.g., 'n.name')
                        $values[] = [$value]; // Add the corresponding value in an array for each row
                    }
                }
            }

            return [
                'data' => [
                    [
                        'fields' => $fields,
                        'values' => $values,
                    ],
                ],
            ];
        }

        return $results; // Return unchanged if no transformation is needed
    }

    public static function queryProvider(): array
    {
        return [
            // Basic test with exact names
            'testWithExactNames' => [
                'https://bb79fe35.databases.neo4j.io',
                'neo4j',
                'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0',
                'MATCH (n:Person) WHERE n.name IN $names RETURN n.name',
                ['names' => ['bob1', 'alicy']],
                [
                    'data' => [
                        ['fields' => ['n.name'], 'values' => [['bob1'], ['alicy']]],
                    ],
                ],
            ],
            // Test with a single name
            'testWithSingleName' => [
                'https://bb79fe35.databases.neo4j.io',
                'neo4j',
                'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0',
                'MATCH (n:Person) WHERE n.name = $name RETURN n.name',
                ['name' => 'bob1'],
                [
                    'data' => [
                        ['fields' => ['n.name'], 'values' => [['bob1']]],
                    ],
                ],
            ],
            // Test for relationship data type
            'testRelationshipType' => [
                'https://your-neo4j-instance',
                'neo4j',
                'your-password',
                'MATCH (a:Person {name: $name1}), (b:Person {name: $name2}) CREATE (a)-[r:FRIENDS_WITH]->(b) RETURN r',
                ['name1' => 'Alice', 'name2' => 'Bob'],
                [
                    'data' => [
                        ['fields' => ['r'], 'values' => [[
                            '_type' => 'FRIENDS_WITH',
                            '_properties' => [],
                        ]]],
                    ],
                ],
            ],
            // Test with no matching names
            'testWithNoMatchingNames' => [
                'https://bb79fe35.databases.neo4j.io',
                'neo4j',
                'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0',
                'MATCH (n:Person) WHERE n.name IN $names RETURN n.name',
                ['names' => ['charlie', 'david']],
                [
                    'data' => [],
                ],
            ],
            // Additional test cases as needed...
            // Test with string data type
            'testWithString' => [
                'https://your-neo4j-instance',
                'neo4j',
                'your-password',
                'CREATE (n:Person {name: $name}) RETURN n.name',
                ['name' => 'Alice'],
                [
                    'data' => [
                        ['fields' => ['n.name'], 'values' => [['Alice']]],
                    ],
                ],
            ]
        ];
    }
}
