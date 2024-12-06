<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use GuzzleHttp\Exception\GuzzleException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIIntegrationTest extends TestCase
{
    /**
     * @throws GuzzleException
     */
    public static function setUpBeforeClass(): void
    {
        $api = Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS'),
            getenv('NEO4J_USERNAME'),
            getenv('NEO4J_PASSWORD')
        );

        // Clear the database
        self::clearDatabase($api);

        // Create necessary constraints
        self::createConstraints($api);

        // Insert test data
        self::populateFixtures($api, ['bob1', 'alicy']);

        // Validate fixtures
        self::validateFixtures($api);
    }

    /**
     * @throws GuzzleException
     */
    private static function clearDatabase(Neo4jQueryAPI $api): void
    {
        $api->run('MATCH (n) DETACH DELETE n',[]);
    }

    /**
     * @throws GuzzleException
     */
    private static function createConstraints(Neo4jQueryAPI $api): void
    {
        $api->run('CREATE CONSTRAINT IF NOT EXISTS FOR (p:Person1) REQUIRE p.name IS UNIQUE',[]);
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
        $results = $api->run('MATCH (p:Person) RETURN p.name',[]);
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
        array $parameters,
        array $expectedResults
    ): void {
        $api = Neo4jQueryAPI::login($address, $username, $password);
        $results = $api->run($query, $parameters);

        // Remove bookmarks if present
        unset($results['bookmarks']);

        $this->assertIsArray($results);
        $this->assertEquals($expectedResults, $results);
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
                        ['row' => ['n.name' => 'bob1']],
                        ['row' => ['n.name' => 'alicy']],
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

            // Test with a single name
            'testWithSingleName' => [
                'https://bb79fe35.databases.neo4j.io',
                'neo4j',
                'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0',
                'MATCH (n:Person) WHERE n.name = $name RETURN n.name',
                ['name' => 'bob1'],
                [
                    'data' => [
                        ['row' => ['n.name' => 'bob1']],
                    ],
                ],
            ],

            // Test for non-existent label
            'testWithNonExistentLabel' => [
                'https://bb79fe35.databases.neo4j.io',
                'neo4j',
                'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0',
                'MATCH (n:NonExistentLabel) RETURN n',
                [],
                [
                    'data' => [],
                ],
            ],


            // Test with an invalid query that should return an error
            'testWithInvalidQuery' => [
                'https://bb79fe35.databases.neo4j.io',
                'neo4j',
                'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0',
                'MATCH (n:Person) WHERE n.nonexistentProperty = $value RETURN n.name',
                ['value' => 'someValue'],
                [
                    'data' => [],
                ],
            ],
        ];
    }
}
