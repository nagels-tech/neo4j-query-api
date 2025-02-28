<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Exception\Neo4jException;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\ResponseParser;
use Neo4j\QueryAPI\Results\ResultSet;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class ResponseParserUnitTest extends TestCase
{
    private ResponseParser $parser;
    private OGM $ogmMock;
    private ResponseInterface $responseMock;
    private StreamInterface $streamMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->ogmMock = $this->createMock(OGM::class);
        $this->parser = new ResponseParser($this->ogmMock);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->streamMock = $this->createMock(StreamInterface::class);
    }

    public function testParseRunQueryResponseThrowsExceptionOnErrorResponse(): void
    {
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->streamMock = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock->method('getStatusCode')->willReturn(400);
        $this->responseMock->expects($this->any())->method('getBody')->willReturn($this->streamMock);
        $this->streamMock->method('__toString')->willReturn(json_encode(["error" => "some error"]));

        $this->expectException(Neo4jException::class);
        $this->parser->parseRunQueryResponse($this->responseMock);
    }


    public function testParseRunQueryResponseThrowsExceptionOnInvalidData(): void
    {
        $this->responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamMock);

        $this->streamMock->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode([]));

        $this->expectException(RuntimeException::class);

        $this->parser->parseRunQueryResponse($this->responseMock);
    }

    public function testParseRunQueryResponseHandlesProfiledQueryPlan(): void
    {
        $data = [
            'data' => [
                'fields' => ['name'],
                'values' => [['Neo4j']]
            ],
            'profiledQueryPlan' => [
                'operatorType' => 'SomeOperator',
                'arguments' => ['planner' => 'IDP'],
                'children' => []
            ],
            'accessMode' => 'READ'
        ];

        $this->responseMock->expects($this->any())->method('getStatusCode')->willReturn(200);

        $this->responseMock->method('getBody')->willReturn($this->streamMock);
        $this->streamMock->method('getContents')->willReturn(json_encode($data));

        $resultSet = $this->parser->parseRunQueryResponse($this->responseMock);

        $this->assertInstanceOf(ResultSet::class, $resultSet);

        $reflection = new \ReflectionClass($resultSet);
        $property = $reflection->getProperty('profiledQueryPlan');
        $profiledQueryPlan = $property->getValue($resultSet);

        $this->assertInstanceOf(ProfiledQueryPlan::class, $profiledQueryPlan);
        $this->assertEquals('SomeOperator', $profiledQueryPlan->operatorType);
    }

}
