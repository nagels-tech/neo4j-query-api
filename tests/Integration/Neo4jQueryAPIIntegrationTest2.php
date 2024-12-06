<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIIntegrationTest2 extends TestCase
{

    public static function setUpBeforeClass(): void
    {
        $api = Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS'),
            getenv('NEO4J_USERNAME'),
            getenv('NEO4J_PASSWORD')
        );

        // Clear the database
        self::clearDatabase($api);

        // Create necessary constraints (optional)
        self::createConstraints($api);

        // Insert test data
        self::populateFixtures($api);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->address = getenv('NEO4J_ADDRESS');
        $this->username = getenv('NEO4J_USERNAME');
        $this->password = getenv('NEO4J_PASSWORD');
        $this->query = 'MATCH (n:Person) RETURN n.name LIMIT 2';
    }

    // Function to clear the Neo4j database
    private static function clearDatabase(Neo4jQueryAPI $api): void
    {
        // Clear the database using MATCH DETACH DELETE
        $api->run('MATCH (n) DETACH DELETE n', []);
    }

    // Function to create constraints in Neo4j
    private static function createConstraints(Neo4jQueryAPI $api): void
    {
        // Create constraints (for example: ensure uniqueness of the name property for Person nodes)
        $api->run('CREATE CONSTRAINT IF NOT EXISTS FOR (p:Person) REQUIRE p.name IS UNIQUE', []);
    }

    // Function to populate the Neo4j database with test data
    private static function populateFixtures(Neo4jQueryAPI $api): void
    {
        // Insert test data into the Neo4j database
        $api->run('CREATE (:Person {name: $name})', ['name' => 'neo4j-php-client']);
        $api->run('CREATE (:Person {name: $name})', ['name' => 'neo4j-symfony']);
    }

    #[DataProvider(methodName: 'queryProvider')]
    public function testRunSuccessWithParameters(
        string $address,
        string $username,
        string $password,
        string $query,
        array $expectedResults
    ): void {
        $api = Neo4jQueryAPI::login($address, $username, $password);
        $results = $api->run($query, []);

        if (isset($results['bookmarks'])) {
            unset($results['bookmarks']);
        }

        $this->assertIsArray($results);
        $this->assertEquals($expectedResults, $results);
    }

    public static function queryProvider(): array
    {
        return [
            [
                'https://bb79fe35.databases.neo4j.io',
                'neo4j',
                'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0',
                'MATCH (n:Person) RETURN n.name LIMIT 2',
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
