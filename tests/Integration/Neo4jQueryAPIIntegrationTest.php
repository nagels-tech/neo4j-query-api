<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use GuzzleHttp\Exception\GuzzleException;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Transaction;

class Neo4jQueryAPIIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    /**
     * @throws GuzzleException
     */
    public function setUp(): void
    {
        $this->api = $this->initializeApi();

        // Clear database and populate test data
        $this->clearDatabase();
        $this->populateTestData();
    }

    private function initializeApi(): Neo4jQueryAPI
    {
        return Neo4jQueryAPI::login(
            getenv('NEO4J_ADDRESS'),
            getenv('NEO4J_USERNAME'),
            getenv('NEO4J_PASSWORD')
        );
    }

    public function testCounters(): void
    {
        $result = $this->api->run('CREATE (x:Node {hello: "world"})');

        $this->assertEquals(1, $result->getQueryCounters()->getNodesCreated());
    }

    public function testCreateBookmarks(): void
    {
        $result = $this->api->run(cypher: 'CREATE (x:Node {hello: "world"})');

        $bookmarks = $result->getBookmarks();

        $result = $this->api->run('CREATE (x:Node {hello: "world2"})');

        $bookmarks->addBookmarks($result->getBookmarks());

        $result = $this->api->run(cypher: 'MATCH (x:Node {hello: "world2"}) RETURN x', bookmark: $bookmarks);

        $this->assertCount(1, $result);
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
            //           $errorMessages = $e->getErrorType() . $e->errorSubType() . $e->errorName();
            $this->assertInstanceOf(Neo4jException::class, $e);
            $this->assertEquals('Neo.ClientError.Schema.EquivalentSchemaRuleAlreadyExists', $e->getErrorCode());
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function testWithExactNames(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.name' => 'bob1']),
                new ResultRow(['n.name' => 'alicy']),
            ],
            new ResultCounters(),
            new Bookmarks([])
        );

        $results = $this->api->run('MATCH (n:Person) WHERE n.name IN $names RETURN n.name', [
            'names' => ['bob1', 'alicy']
        ]);

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithSingleName(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.name' => 'bob1']),
            ],
            new ResultCounters(),
            new Bookmarks([])
        );

        $results = $this->api->run('MATCH (n:Person) WHERE n.name = $name RETURN n.name', [
            'name' => 'bob1'
        ]);

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithInteger(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.age' => 30]),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run('CREATE (n:Person {age: $age}) RETURN n.age', [
            'age' => '30'
        ]);

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithFloat(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.height' => 1.75]),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run('CREATE (n:Person {height: $height}) RETURN n.height', [
            'height' => 1.75
        ]);

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithNull(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.middleName' => null]),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 0,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run('CREATE (n:Person {middleName: $middleName}) RETURN n.middleName', [
            'middleName' => null
        ]);

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithBoolean(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.isActive' => true]),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run('CREATE (n:Person {isActive: $isActive}) RETURN n.isActive', [
            'isActive' => true
        ]);

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithString(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.name' => 'Alice']),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run('CREATE (n:Person {name: $name}) RETURN n.name', [
            'name' => 'Alice'
        ]);

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithArray(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.name' => 'bob1']),
                new ResultRow(['n.name' => 'alicy'])
            ],
            new ResultCounters(
                containsUpdates: false,
                nodesCreated: 0,
                propertiesSet: 0,
                labelsAdded: 0,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'MATCH (n:Person) WHERE n.name IN $names RETURN n.name',
            ['names' => ['bob1', 'alicy']]
        );

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithDate(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.date' => '2024-12-11T11:00:00Z'])

            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'CREATE (n:Person {date: datetime($date)}) RETURN n.date',
            ['date' => "2024-12-11T11:00:00Z"]
        );

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithDuration(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.duration' => 'P14DT16H12M']),

            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'CREATE (n:Person {duration: duration($duration)}) RETURN n.duration',
            ['duration' => 'P14DT16H12M'],
        );

        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithWGS84_2DPoint(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.Point' => 'SRID=4326;POINT (1.2 3.4)']),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'CREATE (n:Person {Point: point($Point)}) RETURN n.Point',
            [
                'Point' => [
                    'longitude' => 1.2,
                    'latitude' => 3.4,
                    'crs' => 'wgs-84',
                ]]
        );


        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithWGS84_3DPoint(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.Point' => 'SRID=4979;POINT (1.2 3.4 4.2)']),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'CREATE (n:Person {Point: point({longitude: $longitude, latitude: $latitude, height: $height, srid: $srid})}) RETURN n.Point',
            [
                'longitude' => 1.2,
                'latitude' => 3.4,
                'height' => 4.2,
                'srid' => 4979,
            ]
        );


        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithCartesian2DPoint(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.Point' => 'SRID=7203;POINT (10.5 20.7)']),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'CREATE (n:Person {Point: point({x: $x, y: $y, srid: $srid})}) RETURN n.Point',
            [
                'x' => 10.5,
                'y' => 20.7,
                'srid' => 7203,
            ]
        );


        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithCartesian3DPoint(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['n.Point' => 'SRID=9157;POINT (10.5 20.7 30.9)']),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'CREATE (n:Person {Point: point({x: $x, y: $y, z: $z, srid: $srid})}) RETURN n.Point',
            [
                'x' => 10.5,
                'y' => 20.7,
                'z' => 30.9,
                'srid' => 9157,
            ]
        );


        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithNode(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow([
                    'node' => [
                        'properties' => [
                            'name' => 'Ayush',
                            'location' => 'New York',
                            'age' => '30'
                        ],
                        'labels' => [
                            0 => 'Person'
                        ]

                    ]
                ]),
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 3,
                labelsAdded: 1,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'CREATE (n:Person {name: $name, age: $age, location: $location}) RETURN {labels: labels(n), properties: properties(n)} AS node',
            [
                'name' => 'Ayush',
                'age' => 30,
                'location' => 'New York',
            ]
        );


        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithPath(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['node1' => [
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
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 2,
                propertiesSet: 2,
                relationshipsCreated: 1,
                labelsAdded: 2,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'CREATE (a:Person {name: $name1}), (b:Person {name: $name2}),
     (a)-[r:FRIENDS]->(b)
     RETURN {labels: labels(a), properties: properties(a)} AS node1,
            {labels: labels(b), properties: properties(b)} AS node2,
            collect(type(r)) AS relationshipTypes',
            [
                'name1' => 'A',
                'name2' => 'B',
            ]
        );


        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }


    public function testWithMap(): void
    {
        $expected = new ResultSet(
            [
                new ResultRow(['map' => [
                    'hello' => 'hello',
                ],
                ]),
            ],
            new ResultCounters(
                containsUpdates: false,
                nodesCreated: 0,
                propertiesSet: 0,
                labelsAdded: 0,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
            'RETURN {hello: "hello"} AS map',
            []
        );


        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }

    public function testWithRelationship(): void
    {
        $expected = new ResultSet(
            [
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
            ],
            new ResultCounters(
                containsUpdates: true,
                nodesCreated: 2,
                propertiesSet: 6,
                relationshipsCreated: 1,
                labelsAdded: 2,
            ),
            new Bookmarks([])
        );

        $results = $this->api->run(
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
                'location2' => 'Los Angeles'
            ]
        );


        $this->assertEquals($expected->getQueryCounters(), $results->getQueryCounters());
        $this->assertEquals(iterator_to_array($expected), iterator_to_array($results));
        $this->assertCount(1, $results->getBookmarks());
    }


}
