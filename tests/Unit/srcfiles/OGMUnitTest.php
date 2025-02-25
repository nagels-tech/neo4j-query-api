<?php

namespace Neo4j\QueryAPI\Tests\Unit\srcfiles;

use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Relationship;
use Neo4j\QueryAPI\Objects\Point;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 *  @api
 */
class OGMUnitTest extends TestCase
{
    private OGM $ogm;

    #[\Override]
    protected function setUp(): void
    {
        $this->ogm = new OGM();
    }

    public function testMapInteger(): void
    {
        $data = ['$type' => 'Integer', '_value' => 42];
        $this->assertSame(42, $this->ogm->map($data));
    }

    public function testMapFloat()
    {
        $data = ['$type' => 'Float', '_value' => 3.14];
        $this->assertSame(3.14, $this->ogm->map($data));
    }

    public function testMapString()
    {
        $data = ['$type' => 'String', '_value' => 'hello'];
        $this->assertSame('hello', $this->ogm->map($data));
    }

    public function testMapBoolean()
    {
        $data = ['$type' => 'Boolean', '_value' => true];
        $this->assertTrue($this->ogm->map($data));
    }

    public function testMapNull()
    {
        $data = ['$type' => 'Null', '_value' => null];
        $this->assertNull($this->ogm->map($data));
    }

    public function testMapArray()
    {
        $data = ['$type' => 'List', '_value' => [['$type' => 'Integer', '_value' => 1], ['$type' => 'Integer', '_value' => 2]]];
        $this->assertSame([1, 2], $this->ogm->map($data));
    }

    public function testMapNode()
    {
        $data = [
            '$type' => 'Node',
            '_value' => ['_labels' => ['Person'], '_properties' => ['name' => ['$type' => 'String', '_value' => 'Alice']]]
        ];

        $result = $this->ogm->map($data);
        $this->assertInstanceOf(Node::class, $result);
        $this->assertSame(['Person'], $result->getLabels());
        $this->assertSame(['name' => 'Alice'], $result->getProperties());
    }

    public function testMapRelationship()
    {
        $data = [
            '$type' => 'Relationship',
            '_value' => ['_type' => 'KNOWS', '_properties' => ['since' => ['$type' => 'Integer', '_value' => 2020]]]
        ];

        $result = $this->ogm->map($data);
        $this->assertInstanceOf(Relationship::class, $result);
        $this->assertSame('KNOWS', $result->getType());
        $this->assertSame(['since' => 2020], $result->getProperties());
    }

    public function testMapPoint(): void
    {
        $data = ['$type' => 'Point', '_value' => 'SRID=4326;POINT (30 10)'];
        $result = $this->ogm->map($data);

        $this->assertInstanceOf(Point::class, $result);
        $this->assertSame(30.0, $result->getX());
        $this->assertSame(10.0, $result->getY());
        $this->assertSame(4326, $result->getSrid());
    }

    public function testParseWKT()
    {
        $wkt = 'SRID=4326;POINT (10 20 30)';
        $result = OGM::parseWKT($wkt);

        $this->assertInstanceOf(Point::class, $result);
        $this->assertSame(10.0, $result->getX());
        $this->assertSame(20.0, $result->getY());
        $this->assertSame(30.0, $result->getZ());
        $this->assertSame(4326, $result->getSrid());
    }

    public function testInvalidWKTThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        OGM::parseWKT('Invalid WKT String');
    }

    public function testInvalidPointFormatThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->ogm->map(['$type' => 'Point', '_value' => 'Invalid Point Format']);
    }
}
