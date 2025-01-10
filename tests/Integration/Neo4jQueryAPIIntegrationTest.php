<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Neo4jQueryAPIIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;


    /**
     * @throws GuzzleException
     */
    public function setUp(): void
    {


        $this->api = $this->initializeApi();

        $this->clearDatabase();
        $this->populateTestData();
    }

    private function initializeApi(): Neo4jQueryAPI
    {
        return Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS') ?: '***REMOVED***/',
            getenv('NEO4J_USERNAME') ?: 'neo4j',
            getenv('NEO4J_PASSWORD') ?: '***REMOVED***'
        );
    }

    public function testCounters(): void
    {
        $result = $this->api->run('CREATE (x:Node {hello: "world"})');

        $this->assertEquals(1, $result->getQueryCounters()->getNodesCreated());
    }

    public function testProfileExistence(): void
    {
        $query = "PROFILE MATCH (n:Person) RETURN n.name";
        $result = $this->api->run($query);
        $this->assertNotNull($result->getProfiledQueryPlan(), "profiled query plan not found");
    }

    public function testProfileCreateQueryExistence(): void
    {
        // Define the CREATE query
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

        $this->assertNotNull($result->getProfiledQueryPlan(), "profiled query plan not found");
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

        $this->assertNotNull($result->getProfiledQueryPlan(), "profiled query plan not found");
    }

    public function testProfileCreateFriendsQueryExistence(): void
    {
        $query = "
    PROFILE UNWIND range(1, 100) AS i
    UNWIND range(1, 100) AS j
    MATCH (a:Person {id: i}), (b:Person {id: j})
    WHERE a.id <> b.id AND rand() < 0.1
    CREATE (a)-[:FRIENDS]->(b);
    ";

        $result = $this->api->run($query);


        $this->assertNotNull($result->getProfiledQueryPlan(), "profiled query plan not found");
    }

    public function testProfileCreateWatchedRelationshipExistence(): void
    {

        $query = "
    PROFILE UNWIND range(1, 50) AS i
    MATCH (p:Person), (m:Movie {year: 2000 + i})
    CREATE (p)-[:WATCHED]->(m);
    ";

        $result = $this->api->run($query);

        $this->assertNotNull($result->getProfiledQueryPlan(), "profiled query plan not found");
    }

    public function testProfileCreateWatchedWithFilters(): void
    {
        $query = "
    PROFILE UNWIND range(1, 50) AS i
    MATCH (p:Person), (m:Movie {year: 2000 + i})
    WHERE p.age > 25 AND m.genre = 'Action'
    CREATE (p)-[:WATCHED]->(m);
    ";

        $result = $this->api->run($query);
        $this->assertNotNull($result->getProfiledQueryPlan(), "profiled query plan not found");
    }

   public function testProfileCreateKnowsBidirectionalRelationshipsMock(): void
    {
        $query = "
    PROFILE UNWIND range(1, 100) AS i
    UNWIND range(1, 100) AS j
    MATCH (a:Person {id: i}), (b:Person {id: j})
    WHERE a.id < b.id AND rand() < 0.1
    CREATE (a)-[:KNOWS]->(b), (b)-[:KNOWS]->(a);
    ";

        $mockSack = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/../resources/responses/complex-query-profile.json')),
        ]);

        $handler = HandlerStack::create($mockSack);
        $client = new Client(['handler' => $handler]);
        $api = new Neo4jQueryAPI($client);

        $result = $api->run($query);
        $plan = $result->getProfiledQueryPlan();

        $expected = require __DIR__.'/../resources/expected/complex-query-profile.php';

        $this->assertEquals($expected, "profiled query plan not found");
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
        $this->assertNotEmpty($profiledQueryPlan->getChildren());

        foreach ($profiledQueryPlan->getChildren() as $child) {
            $this->assertInstanceOf(ProfiledQueryPlan::class, $child);
        }
    }



    public function testTransactionCommit(): void
    {
        // Begin a new transaction
        $tsx = $this->api->beginTransaction();

        // Generate a random name for the node
        $name = (string)mt_rand(1, 100000);

        // Create a node within the transaction
        $tsx->run("CREATE (x:Human {name: \$name})", ['name' => $name]);

        // Validate that the node does not exist in the database before the transaction is committed
        $results = $this->api->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(0, $results);

        // Validate that the node exists within the transaction
        $results = $tsx->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(1, $results);

        // Commit the transaction
        $tsx->commit();

        // Validate that the node now exists in the database
        $results = $this->api->run("MATCH (x:Human {name: \$name}) RETURN x", ['name' => $name]);
        $this->assertCount(1, $results); // Updated to expect 1 result
    }


    /**
     * @throws GuzzleException
     */
    private function clearDatabase(): void
    {
        $this->api->run('MATCH (n) DETACH DELETE n', []);
    }

    /**
     * @throws GuzzleException
     */
    private function populateTestData(): void
    {
        $names = ['bob1', 'alicy'];
        foreach ($names as $name) {
            $this->api->run('CREATE (:Person {name: $name})', ['name' => $name]);
        }
    }

    /**
     * @throws GuzzleException
     */
    /*#[DataProvider(methodName: 'queryProvider')]
    public function testRunSuccessWithParameters(
        string    $query,
        array     $parameters,
        ResultSet $expectedResults
    ): void
    {
        $results = $this->api->run($query, $parameters);
        $this->assertEquals($expectedResults, $results);
    }*/

    public function testInvalidQueryException(): void
    {
        try {
            $this->api->run('CREATE (:Person {createdAt: $invalidParam})', [
                'date' => new \DateTime('2000-01-01 00:00:00')
            ]);
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Neo4jException::class, $e);
            $this->assertEquals('Neo.ClientError.Statement.ParameterMissing', $e->getErrorCode());
            $this->assertEquals('Expected parameter(s): invalidParam', $e->getMessage());
        }
    }


    public function testCreateDuplicateConstraintException(): void
    {
        try {
            $this->api->run('CREATE CONSTRAINT person_name FOR (n:Person1) REQUIRE n.name IS UNIQUE', []);
            $this->fail('Expected a Neo4jException to be thrown.');
        } catch (Neo4jException $e) {
//            $errorMessages = $e->getErrorType() . $e->errorSubType() . $e->errorName();
            $this->assertInstanceOf(Neo4jException::class, $e);
            $this->assertEquals('Neo.ClientError.Schema.EquivalentSchemaRuleAlreadyExists', $e->getErrorCode());
            $this->assertNotEmpty($e->getMessage());
        }
    }


    public static function queryProvider(): array
    {

        return [
            'testWithExactNames' => [
                'MATCH (n:Person) WHERE n.name IN $names RETURN n.name',
                ['names' => ['bob1', 'alicy']],
                new ResultSet([
                    new ResultRow(['n.name' => 'bob1']),
                    new ResultRow(['n.name' => 'alicy']),
                ])
            ],
            'testWithSingleName' => [
                'MATCH (n:Person) WHERE n.name = $name RETURN n.name',
                ['name' => 'bob1'],
                new ResultSet([
                    new ResultRow(['n.name' => 'bob1']),
                ])
            ],


            'testWithInteger' => [
                'CREATE (n:Person {age: $age}) RETURN n.age',
                ['age' => 30],
                new ResultSet([
                    new ResultRow(['n.age' => 30]),
                ])
            ],

            'testWithFloat' => [
                'CREATE (n:Person {height: $height}) RETURN n.height',
                ['height' => 1.75],
                new ResultSet(
                    [
                        new ResultRow(['n.height' => 1.75]),
                    ]
                ),
            ],

            'testWithNull' => [
                'CREATE (n:Person {middleName: $middleName}) RETURN n.middleName',
                ['middleName' => null],
                new ResultSet(
                    [
                        new ResultRow(['n.middleName' => null]),
                    ])
            ],

            'testWithBoolean' => [
                'CREATE (n:Person {isActive: $isActive}) RETURN n.isActive',
                ['isActive' => true],
                new ResultSet(
                    [
                        new ResultRow(['n.isActive' => true]),
                    ])
            ],

            'testWithString' => [
                'CREATE (n:Person {name: $name}) RETURN n.name',
                ['name' => 'Alice'],
                new ResultSet(
                    [
                        new ResultRow(['n.name' => 'Alice']),
                    ])
            ],

            'testWithArray' => [
                'MATCH (n:Person) WHERE n.name IN $names RETURN n.name',
                ['names' => ['bob1', 'alicy']],
                new ResultSet([
                    new ResultRow(['n.name' => 'bob1']),
                    new ResultRow(['n.name' => 'alicy']),
                ])
            ],


            'testWithDate' => [
                'CREATE (n:Person {date: datetime($date)}) RETURN n.date',
                ['date' => "2024-12-11T11:00:00Z"],
                new ResultSet(
                    [
                        new ResultRow(['n.date' => '2024-12-11T11:00:00Z']),
                    ])
            ],

            'testWithDuration' => [
                'CREATE (n:Person {duration: duration($duration)}) RETURN n.duration',
                ['duration' => 'P14DT16H12M'],
                new ResultSet([
                    new ResultRow(['n.duration' => 'P14DT16H12M']),
                ])
            ],
            'testWithWGS84_2DPoint' => [
                'CREATE (n:Person {Point: point($Point)}) RETURN n.Point',
                [
                    'Point' => [
                        'longitude' => 1.2,
                        'latitude' => 3.4,
                        'crs' => 'wgs-84',
                    ],
                ],
                new ResultSet([
                    new ResultRow(['n.Point' => 'SRID=4326;POINT (1.2 3.4)']),
                ])
            ],
            'testWithWGS84_3DPoint' => [
                'CREATE (n:Person {Point: point({longitude: $longitude, latitude: $latitude, height: $height, srid: $srid})}) RETURN n.Point',
                [
                    'longitude' => 1.2,
                    'latitude' => 3.4,
                    'height' => 4.2,
                    'srid' => 4979,
                ],
                new ResultSet([
                    new ResultRow(['n.Point' => 'SRID=4979;POINT (1.2 3.4 4.2)']),
                ]),
            ],

            'testWithCartesian2DPoint' => [
                'CREATE (n:Person {Point: point({x: $x, y: $y, srid: $srid})}) RETURN n.Point',
                [
                    'x' => 10.5,
                    'y' => 20.7,
                    'srid' => 7203,
                ],
                new ResultSet([
                    new ResultRow([
                        'n.Point' => 'SRID=7203;POINT (10.5 20.7)'
                    ])
                ])
            ],
            'testWithCartesian3DPoint' => [
                'CREATE (n:Person {Point: point({x: $x, y: $y, z: $z, srid: $srid})}) RETURN n.Point',
                [
                    'x' => 10.5,
                    'y' => 20.7,
                    'z' => 30.9,
                    'srid' => 9157,
                ],
                new ResultSet([
                    new ResultRow(['n.Point' => 'SRID=9157;POINT (10.5 20.7 30.9)']),
                ]),
            ],

            'testWithNode' => [
                'CREATE (n:Person {name: $name, age: $age, location: $location}) RETURN {labels: labels(n), properties: properties(n)} AS node',
                [
                    'name' => 'Ayush',
                    'age' => 30,
                    'location' => 'New York',
                ],
                new ResultSet([
                    new ResultRow([
                        'node' => [
                            'labels' => ['Person'],
                            'properties' => [
                                'name' => 'Ayush',
                                'age' => 30,
                                'location' => 'New York',
                            ],
                        ],
                    ]),
                ]),
            ],


            'testWithRelationship' => [
                'CREATE (p1:Person {name: $name1, age: $age1, location: $location1}), 
             (p2:Person {name: $name2, age: $age2, location: $location2}),
             (p1)-[r:FRIEND_OF]->(p2)
     RETURN {labels: labels(p1), properties: properties(p1)} AS node1, 
            {labels: labels(p2), properties: properties(p2)} AS node2,
            type(r) AS relationshipType',
                [
                    'name1' => 'Ayush',
                    'age1' => 30,
                    'location1' => 'New York',
                    'name2' => 'John',
                    'age2' => 25,
                    'location2' => 'Los Angeles',
                ],
                new ResultSet([
                    new ResultRow([
                        'node1' => [
                            'labels' => ['Person'],
                            'properties' => [
                                'name' => 'Ayush',
                                'age' => 30,
                                'location' => 'New York',
                            ],
                        ],
                        'node2' => [
                            'labels' => ['Person'],
                            'properties' => [
                                'name' => 'John',
                                'age' => 25,
                                'location' => 'Los Angeles',
                            ],
                        ],
                        'relationshipType' => 'FRIEND_OF',
                    ]),
                ]),
            ],
            'testWithPath' => [
                'CREATE (a:Person {name: $name1}), (b:Person {name: $name2}),
     (a)-[r:FRIENDS]->(b)
     RETURN {labels: labels(a), properties: properties(a)} AS node1, 
            {labels: labels(b), properties: properties(b)} AS node2,
            collect(type(r)) AS relationshipTypes',
                [
                    'name1' => 'A',
                    'name2' => 'B',
                ],
                new ResultSet([
                    new ResultRow([
                        'node1' => [
                            'labels' => ['Person'],
                            'properties' => [
                                'name' => 'A',
                            ],
                        ],
                        'node2' => [
                            'labels' => ['Person'],
                            'properties' => [
                                'name' => 'B',
                            ],
                        ],
                        'relationshipTypes' => ['FRIENDS'],
                    ]),
                ]),
            ],


            'testWithMap' => [
                'RETURN {hello: "hello"} AS map',
                [],
                new ResultSet([
                    new ResultRow([
                        'map' => [
                            'hello' => 'hello',
                        ],
                    ]),
                ]),
            ],


        ];
    }
}