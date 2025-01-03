<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\TestCase;

class Neo4jQueryApiIntegrationTempTest extends TestCase
{
    private Neo4jQueryAPI $api;

    public function executeQueryWithOgm(string $query, array $parameters = [], string $database = 'neo4j', bool $extended = false)
    {
        $ogm = new OGM();

        $response = $this->executeQuery($query, $parameters, $database);

        return new ResultSet($ogm, $response);
    }

    public function setUp(): void
    {
        $this->api = $this->initializeApi();

        $this->clearDatabase();

        //$this->setupConstraints();

        $this->populateTestData(['bob1', 'alicy']);

        //$this->api->validateData();
    }

    private function initializeApi(): Neo4jQueryAPI
    {
        return Neo4jQueryAPI::login(
            'https://f2455ee6.databases.neo4j.io',
            'neo4j',
            'h5YLhuoSnPD6yMy8OwmFPXs6WkL8uX25zxHCKhiF_hY'
        );
    }

    private function clearDatabase(): void
    {
        $this->api->run('MATCH (n) DETACH DELETE n', [], 'neo4j');
    }

    private function populateTestData(array $names): void
    {
        foreach ($names as $name) {
            $this->api->run('CREATE (:Person {name: $name})', ['name' => $name], 'neo4j');
        }
    }

    public function testResultRowIntegration(): void
    {
        $resultSet = $this->api->run('MATCH (p:Person) RETURN p.name AS name, p.email AS email, p.age AS age, p AS person', [], 'neo4j');

        foreach ($resultSet as $resultRow) {
            $name = $resultRow->get('name');
            $email = $resultRow->get('email');
            $age = $resultRow->get('age');

            echo "Name: $name, Email: $email, Age: $age\n";
        }
    }
}
