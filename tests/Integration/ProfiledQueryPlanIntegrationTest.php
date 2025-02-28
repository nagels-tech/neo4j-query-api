<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Configuration;
use Neo4j\QueryAPI\ResponseParser;
use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\Neo4jRequestFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Http\Discovery\Psr17Factory;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Tests\CreatesQueryAPI;
use PHPUnit\Framework\TestCase;

class ProfiledQueryPlanIntegrationTest extends TestCase
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

    public function testProfileCreateKnowsBidirectionalRelationships(): void
    {
        $query = "
    PROFILE UNWIND range(1, 100) AS i
    UNWIND range(1, 100) AS j
    MATCH (a:Person {id: i}), (b:Person {id: j})
    WHERE a.id < b.id AND rand() < 0.1
    CREATE (a)-[:KNOWS]->(b), (b)-[:KNOWS]->(a);
    ";

        $result = $this->api->run($query);
        $this->assertNotNull($result->getProfiledQueryPlan(), "profiled query plan not found");
        $body = file_get_contents(__DIR__ . '/../resources/responses/complex-query-profile.json');
        $mockSack = new MockHandler([
            new Response(200, [], $body),
        ]);

        $handler = HandlerStack::create($mockSack);
        $client = new Client(['handler' => $handler]);
        $auth = Authentication::fromEnvironment();

        $api = new Neo4jQueryAPI(
            $client,
            new ResponseParser(new OGM()),
            new Neo4jRequestFactory(
                new Psr17Factory(),
                new Psr17Factory(),
                new Configuration('ABC'),
                $auth
            ),
            new Configuration('ABC'),
        );

        $result = $api->run($query);

        $plan = $result->getProfiledQueryPlan();
        $this->assertNotNull($plan, "The result of the query should not be null.");

        $expected = require __DIR__ . '/../resources/expected/complex-query-profile.php';

        $this->assertEquals($expected->getProfiledQueryPlan(), $plan, "Profiled query plan does not match the expected value.");
    }
    public function testProfileCreateActedInRelationships(): void
    {
        $query = "
    PROFILE UNWIND range(1, 50) AS i
    MATCH (p:Person {id: i}), (m:Movie {year: 2000 + i})
    WHERE p.job = 'Artist'
    CREATE (p)-[:ACTED_IN]->(m);
    ";

        $result = $this->api->run($query);
        $this->assertNotNull($result->getProfiledQueryPlan(), "profiled query plan not found");
    }

    public function testChildQueryPlanExistence(): void
    {
        $result = $this->api->run("PROFILE MATCH (n:Person {name: 'Alice'}) RETURN n.name");

        $profiledQueryPlan = $result->getProfiledQueryPlan();
        $this->assertNotNull($profiledQueryPlan);
        $this->assertNotEmpty($profiledQueryPlan->children);

        foreach ($profiledQueryPlan->children as $child) {
            $this->assertInstanceOf(ProfiledQueryPlan::class, $child);
        }
    }
}
