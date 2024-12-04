<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class Neo4jQueryAPIIntegrationTest1 extends TestCase
{
    private string $address;
    private string $username;
    private string $password;
    private string $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->address = 'https://bb79fe35.databases.neo4j.io';
        $this->username = 'neo4j';
        $this->password = 'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0';
        $this->query = 'MATCH (n:Person) WHERE n.name IN ["neo4j-php-client", "neo4j-symfony"] RETURN n.name';
    }

    /**
     * Test method using the data provider.
     *
     * @dataProvider queryProvider
     */
    public function testRunSuccessWithParameters(
        string $address,
        string $username,
        string $password,
        string $query,
        array $expectedResults
    ): void {
        $api = Neo4jQueryAPI::login($address, $username, $password);
        $results = $api->run($query);

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