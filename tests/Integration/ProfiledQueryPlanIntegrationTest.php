<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Tests\CreatesQueryAPI;
use PHPUnit\Framework\TestCase;

final class ProfiledQueryPlanIntegrationTest extends TestCase
{
    use CreatesQueryAPI;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createQueryAPI();
    }

    public function testProfileExistence(): void
    {
        $query = "PROFILE MATCH (n:Person) RETURN n.name";
        $result = $this->api->run($query);
        $this->assertNotNull($result->profiledQueryPlan, "Profiled query plan not found");
    }

    public function testProfileCreateQueryExistence(): void
    {
        $query = "
        PROFILE UNWIND range(1, 100) AS i
        CREATE (:Person {
            name: 'Person' + toString(i),
            id: i,
            job: CASE 
                WHEN i % 2 = 0 THEN 'Engineer'
                ELSE 'Artist'
            END,
            age: 1 + i - 1
        });
        ";

        $result = $this->api->run($query);
        $this->assertNotNull($result->profiledQueryPlan, "Profiled query plan not found");
    }

    public function testProfileCreateMovieQueryExistence(): void
    {
        $query = "
        PROFILE UNWIND range(1, 50) AS i
        CREATE (:Movie {
            year: 2000 + i,
            genre: CASE 
                WHEN i % 2 = 0 THEN 'Action'
                ELSE 'Comedy'
            END,
            title: 'Movie' + toString(i)
        });
        ";

        $result = $this->api->run($query);
        $this->assertNotNull($result->profiledQueryPlan, "Profiled query plan not found");
    }

    public function testProfileCreateFriendsQueryExistence(): void
    {
        $query = "
        PROFILE MATCH (a:Person), (b:Person)
        WHERE a.name = 'Alice' AND b.name = 'Bob'
        CREATE (a)-[:FRIENDS_WITH]->(b);
        ";

        $result = $this->api->run($query);
        $this->assertNotNull($result->profiledQueryPlan, "Profiled query plan not found");
    }
}
