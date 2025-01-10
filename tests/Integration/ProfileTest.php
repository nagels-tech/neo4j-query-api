<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Neo4j\QueryAPI\Profile;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    public function testExecuteQuery(): void
    {
        $mockResponseData = [
            'result' => [
                'columns' => ['name'],
                'rows' => [['John Doe']],
            ],
            'bookmarks' => ['bookmark1'],
        ];


        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponseData))
        ]);
        $handlerStack = HandlerStack::create($mock);

        $mockClient = new Client(['handler' => $handlerStack]);

        $profile = new Profile('http://mock-neo4j-url', 'user', 'password');
        $reflection = new \ReflectionClass(Profile::class);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setValue($profile, $mockClient);

        $query = 'MATCH (n:Person) RETURN n.name';
        $result = $profile->executeQuery($query);

        $this->assertIsArray($result);
        $this->assertEquals($mockResponseData, $result);
    }

    public function testFormatResponse(): void
    {
        $mockInputData = [
            'result' => [
                'columns' => ['name'],
                'rows' => [['John Doe']],
            ],
            'profiledQueryPlan' => [
                'plan' => 'Mock Plan',
            ],
            'bookmarks' => ['bookmark1'],
        ];

        $expectedOutput = [
            'data' => [
                'fields' => ['name'],
                'values' => [['John Doe']],
            ],
            'profiledQueryPlan' => [
                'plan' => 'Mock Plan',
            ],
            'bookmarks' => ['bookmark1'],
        ];

        $profile = new Profile('http://mock-neo4j-url', 'user', 'password');

        $formattedResponse = $profile->formatResponse($mockInputData);
        $this->assertEquals($expectedOutput, $formattedResponse);
    }
}
