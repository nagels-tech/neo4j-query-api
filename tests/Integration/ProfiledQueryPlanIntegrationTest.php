<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Configuration;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Neo4jRequestFactory;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\ResponseParser;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ProfiledQueryPlanIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $neo4jAddress = getenv('NEO4J_ADDRESS');
        if (!is_string($neo4jAddress) || trim($neo4jAddress) === '') {
            throw new \RuntimeException('NEO4J_ADDRESS is not set or is invalid.');
        }

        $this->api = Neo4jQueryAPI::create(
            new Configuration(baseUri: $neo4jAddress),
            Authentication::fromEnvironment()
        );
    }

    public function testProfileExistence(): void
    {
        $query = "PROFILE MATCH (n:Person) RETURN n.name";
        $result = $this->api->run($query);
        $this->assertNotNull($result->getProfiledQueryPlan(), "Profiled query plan not found");
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
        $this->assertNotNull($result->getProfiledQueryPlan(), "Profiled query plan not found");
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
        $this->assertNotNull($result->getProfiledQueryPlan(), "Profiled query plan not found");
    }

    public function testProfileCreateFriendsQueryExistence(): void
    {
        $query = "
        PROFILE MATCH (a:Person), (b:Person)
        WHERE a.name = 'Alice' AND b.name = 'Bob'
        CREATE (a)-[:FRIENDS_WITH]->(b);
        ";

        $result = $this->api->run($query);
        $this->assertNotNull($result->getProfiledQueryPlan(), "Profiled query plan not found");
    }
}
