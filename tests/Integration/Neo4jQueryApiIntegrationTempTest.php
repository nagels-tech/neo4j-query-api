<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Transaction;
use PHPUnit\Framework\TestCase;

class Neo4jQueryApiIntegrationTempTest extends TestCase
{
    private Transaction $api;

    public function setUp(): void
    {
        $this->api = $this->initializeApi();

        $this->clearDatabase();
        $this->setupConstraints();
        $this->api->populateTestData(['bob1', 'alicy']);
        $this->api->validateData();
    }

    private function initializeApi(): Transaction
    {
        return Transaction::login(
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

    public function testResultRowIntegration(): void
    {
        $resultSet = $this->api->run('MATCH (p:Person) RETURN p.name AS name, p.email AS email, p.age AS age, p AS person', []);

        foreach ($resultSet as $resultRow) {

            $name = $resultRow->get('name');
            $email = $resultRow->get('email');
            $age = $resultRow->get('age');

            echo "Name: $name, Email: $email, Age: $age\n";

        }
    }


}