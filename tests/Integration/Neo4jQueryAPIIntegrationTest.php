<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use DateTimeZone;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Objects\Temporal\Date;
use Neo4j\QueryAPI\Objects\Temporal\DateTime;
use Neo4j\QueryAPI\Objects\Temporal\DateTimeZoneId;
use Neo4j\QueryAPI\Objects\Temporal\Duration;
use Neo4j\QueryAPI\Objects\Temporal\LocalDateTime;
use Neo4j\QueryAPI\Objects\Temporal\LocalTime;
use Neo4j\QueryAPI\Objects\Temporal\Time;
use Neo4j\QueryAPI\Results\ResultRow;
use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Enums\AccessMode;
use Throwable;

final class Neo4jQueryAPIIntegrationTest extends TestCase
{
    private Neo4jQueryAPI $api;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->api = $this->initializeApi();
        $this->clearDatabase();
        $this->populateTestData();
    }


    public function testParseRunQueryResponse(): void
    {
        $response = $this->api->run('CREATE (n:TestNode {name: "Test"}) RETURN n');

        $this->assertEquals(new ResultSet(
            rows: [
                new ResultRow([
                    'n' => new Node(
                        ['TestNode'],
                        ['name' => 'Test']
                    )
                ])
            ],
            counters: new ResultCounters(
                containsUpdates: true,
                nodesCreated: 1,
                propertiesSet: 1,
                labelsAdded: 1
            ),
            bookmarks: $response->bookmarks,
            profiledQueryPlan: null,
            accessMode: AccessMode::WRITE
        ), $response);
    }

    public function testInvalidQueryHandling(): void
    {
        $this->expectException(Neo4jException::class);
        $this->api->run('INVALID CYPHER QUERY');
    }

    private function initializeApi(): Neo4jQueryAPI
    {
        $address = getenv('NEO4J_ADDRESS');
        if ($address === false) {
            $address = 'default-address';
        }
        return Neo4jQueryAPI::login($address, Authentication::fromEnvironment());
    }

    public function testCounters(): void
    {
        $result = $this->api->run('CREATE (x:Node {hello: "world"})');
        $queryCounters = $result->counters;

        $this->assertNotNull($queryCounters);
        $this->assertEquals(1, $queryCounters->nodesCreated);
    }

    private function clearDatabase(): void
    {
        $this->api->run('MATCH (n) DETACH DELETE n', []);
    }

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
        } catch (Throwable $e) {
            $this->assertInstanceOf(Neo4jException::class, $e);
            $this->assertEquals('Neo.ClientError.Statement.ParameterMissing', $e->getErrorCode());
            $this->assertEquals('Expected parameter(s): invalidParam', $e->getMessage());
        }
    }

    public function testTemporalDate(): void
    {
        $results = $this->api->run('RETURN date() AS date');

        $this->assertNotEmpty($results->rows);
        $this->assertInstanceOf(ResultRow::class, $results->rows[0]);
        $this->assertArrayHasKey('date', $results->rows[0]->data);

        $date = $results->rows[0]->data['date'];
        $this->assertInstanceOf(Date::class, $date);

        $expectedDays = (new \DateTime("1970-01-01"))->diff(new \DateTime())->format('%r%a');
        $this->assertEquals((int)$expectedDays, $date->days);
    }

    public function testTemporalDateTime(): void
    {
        $results = $this->api->run('RETURN DateTime() AS datetime');

        $this->assertNotEmpty($results->rows);
        $this->assertInstanceOf(ResultRow::class, $results->rows[0]);
        $this->assertArrayHasKey('datetime', $results->rows[0]->data);
        $datetime = $results->rows[0]->data['datetime'];
        $this->assertInstanceOf(DateTime::class, $datetime);

        $this->assertEquals(date_create()->format('Y-m-d'), $datetime->getDateTime()->format('Y-m-d'));
    }

    public function testTemporalDateTimeZoneId(): void
    {
        $results = $this->api->run("RETURN 'America/New_York' AS timezone");

        $this->assertNotEmpty($results->rows);
        $this->assertInstanceOf(ResultRow::class, $results->rows[0]);
        $this->assertArrayHasKey('timezone', $results->rows[0]->data);
        $this->assertInstanceOf(DateTimeZoneId::class, $results->rows[0]->data['timezone']);

        $zoneId = $results->rows[0]->data['timezone']->getZoneId();
        $this->assertEquals('America/New_York', $zoneId);
    }


    public function testTemporalTime(): void
    {
        $results = $this->api->run('RETURN time() AS time');

        $this->assertNotEmpty($results->rows);
        $this->assertInstanceOf(ResultRow::class, $results->rows[0]);
        $this->assertArrayHasKey('time', $results->rows[0]->data);
        $time = $results->rows[0]->data['time'];
        $this->assertInstanceOf(Time::class, $time);

        $neo4jTime = $time->getTime();
        $expectedTime = (new \DateTime())->format('H:i');
        $this->assertStringStartsWith($expectedTime, $neo4jTime);
    }

    public function testTemporalLocalTime(): void
    {
        $results = $this->api->run('RETURN localtime() AS localtime');

        $this->assertNotEmpty($results->rows);
        $this->assertInstanceOf(ResultRow::class, $results->rows[0]);
        $this->assertArrayHasKey('localtime', $results->rows[0]->data);
        $localTime = $results->rows[0]->data['localtime'];
        $this->assertInstanceOf(LocalTime::class, $localTime);
        $neo4jLocalTime = $localTime->getLocalTime();

        $expectedLocalTime = (new \DateTime())->format('H:i');

        $this->assertStringStartsWith($expectedLocalTime, $neo4jLocalTime);
    }

    public function testTemporalLocalDateTime(): void
    {
        $results = $this->api->run('RETURN localdatetime() AS localdatetime');

        $this->assertNotEmpty($results->rows);
        $this->assertInstanceOf(ResultRow::class, $results->rows[0]);
        $this->assertArrayHasKey('localdatetime', $results->rows[0]->data);
        $localDateTime = $results->rows[0]->data['localdatetime'];

        $this->assertInstanceOf(LocalDateTime::class, $localDateTime);

        $neo4jLocalDateTime = $localDateTime->getLocalDateTime();

        $expectedDate = date_create()->format('Y-m-d');

        $this->assertEquals($expectedDate, $neo4jLocalDateTime->format('Y-m-d'));
    }

    public function testTemporalDuration(): void
    {
        $results = $this->api->run("RETURN duration({years: 1, months: 2, days: 10, hours: 5, minutes: 30, seconds: 15}) AS duration");

        $this->assertNotEmpty($results->rows);
        $this->assertInstanceOf(ResultRow::class, $results->rows[0]);
        $this->assertArrayHasKey('duration', $results->rows[0]->data);
        $duration = $results->rows[0]->data['duration'];
        $this->assertInstanceOf(Duration::class, $duration);
        $neo4jDuration = $duration->getDuration();

        $expectedPattern = '/P1Y2M10DT5H30M15S/';

        $this->assertMatchesRegularExpression($expectedPattern, $neo4jDuration);
    }

}
