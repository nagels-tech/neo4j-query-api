<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIIntegrationTest1 extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $api = Neo4jQueryAPI::login(
            'https://bb79fe35.databases.neo4j.io',
            'neo4j',
            'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0'
        );

        self::clearDatabase($api);
        self::populateFixtures($api);
    }

    private static function clearDatabase(Neo4jQueryAPI $api): void
    {
        $api->run('MATCH (n) DETACH DELETE n', []);
    }

    private static function populateFixtures(Neo4jQueryAPI $api): void
    {
        $api->run('CREATE (:Person {name: "neo4j-php-client"})', []);
        $api->run('CREATE (:Person {name: "neo4j-symfony"})', []);
    }

    /**
     * @dataProvider queryProvider
     */
    public function testRunSuccessWithParameters(
        string $address,
        string $username,
        string $password,
        string $query,
        array $expectedResults
    ): void {
        // Login to Neo4j
        $api = Neo4jQueryAPI::login($address, $username, $password);

        // Execute the query
        $results = $api->run($query, []);

        // Remove unnecessary data from results
        if (isset($results['bookmarks'])) {
            unset($results['bookmarks']);
        }

        // Validate the results
        $this->assertIsArray($results);
        $this->assertEquals($expectedResults, $results);
    }

    public static function queryProvider(): array
    {
        return [
            [
                'https://bb79fe35.databases.neo4j.io', // Address
                'neo4j',                              // Username
                'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0', // Password
                'MATCH (n:Person) WHERE n.name IN ["neo4j-php-client", "neo4j-symfony"] RETURN n.name', // Query
                [
                    'data' => [
                        ['row' => ['n.name' => 'neo4j-php-client']],
                        ['row' => ['n.name' => 'neo4j-symfony']],
                    ],
                ],
            ],
        ];
    }
}