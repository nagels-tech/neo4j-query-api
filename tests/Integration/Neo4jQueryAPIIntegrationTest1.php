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

    protected function setUp(): void
    {
        parent::setUp();
        $this->address = 'https://bb79fe35.databases.neo4j.io';
        $this->username = 'neo4j';
        $this->password = 'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0';
        $this->query = 'MATCH (n:Person) WHERE n.name IN ["neo4j-php-client", "neo4j-symfony"] RETURN n.name';
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

    public function testRunSuccessWithParameters(
        string $address,
        string $username,
        string $password,
        string $query,
        array  $expectedResults
    ): void
    {

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
                'MATCH (n:Person) WHERE n.name IN ["neo4j-php-client", "neo4j-symfony"] RETURN n.name',
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
