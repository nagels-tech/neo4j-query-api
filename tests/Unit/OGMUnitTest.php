<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use InvalidArgumentException;
use Neo4j\QueryAPI\Objects\Node;
use Neo4j\QueryAPI\Objects\Point;
use Neo4j\QueryAPI\Objects\Relationship;
use Neo4j\QueryAPI\OGM;
use PHPUnit\Framework\TestCase;

/**
 *  @api
 */
final class OGMUnitTest extends TestCase
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

    public function testMapFloat(): void
    {
        $data = ['$type' => 'Float', '_value' => 3.14];
        $this->assertSame(3.14, $this->ogm->map($data));
    }

    public function testMapString(): void
    {
        $data = ['$type' => 'String', '_value' => 'hello'];
        $this->assertSame('hello', $this->ogm->map($data));
    }

    public function testMapBoolean(): void
    {
        $data = ['$type' => 'Boolean', '_value' => true];
        $this->assertTrue($this->ogm->map($data));
    }

    public function testMapNull(): void
    {
        $data = ['$type' => 'Null', '_value' => null];
        $this->assertNull($this->ogm->map($data));
    }

    public function testMapArray(): void
    {
        $data = ['$type' => 'List', '_value' => [['$type' => 'Integer', '_value' => 1], ['$type' => 'Integer', '_value' => 2]]];
        $this->assertSame([1, 2], $this->ogm->map($data));
    }

    public function testMapNode(): void
    {
        $data = [
            '$type' => 'Node',
            '_value' => ['_labels' => ['Person'], '_properties' => ['name' => ['$type' => 'String', '_value' => 'Alice']]]
        ];

        $result = $this->ogm->map($data);
        $this->assertInstanceOf(Node::class, $result);
        $this->assertSame(['Person'], $result->labels);
        $this->assertSame(['name' => 'Alice'], $result->properties);
    }

    public function testMapRelationship(): void
    {
        $data = [
            '$type' => 'Relationship',
            '_value' => ['_type' => 'KNOWS', '_properties' => ['since' => ['$type' => 'Integer', '_value' => 2020]]]
        ];

        $result = $this->ogm->map($data);
        $this->assertInstanceOf(Relationship::class, $result);
        $this->assertSame('KNOWS', $result->type);
        $this->assertSame(['since' => 2020], $result->properties);
    }

    public function testMapPoint(): void
    {
        $data = ['$type' => 'Point', '_value' => 'SRID=4326;POINT (30 10)'];
        $result = $this->ogm->map($data);

        $this->assertInstanceOf(Point::class, $result);
        $this->assertSame(30.0, $result->x);
        $this->assertSame(10.0, $result->y);
        $this->assertSame(4326, $result->srid);
    }

    public function testParseWKT(): void
    {
        $wkt = 'SRID=4326;POINT (10 20 30)';
        $result = OGM::parseWKT($wkt);

        $this->assertInstanceOf(Point::class, $result);
        $this->assertSame(10.0, $result->x);
        $this->assertSame(20.0, $result->y);
        $this->assertSame(30.0, $result->z);
        $this->assertSame(4326, $result->srid);
    }

    public function testInvalidWKTThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OGM::parseWKT('Invalid WKT String');
    }

    public function testInvalidPointFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->ogm->map(['$type' => 'Point', '_value' => 'Invalid Point Format']);
    }
}
