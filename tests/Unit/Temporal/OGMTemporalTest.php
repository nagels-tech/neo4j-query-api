<?php

namespace Neo4j\QueryAPI\Tests\Unit\Temporal;

use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\OGM;
use Neo4j\QueryAPI\Objects\Temporal\Date;

final class OGMTemporalTest extends TestCase
{
    private OGM $ogm;
    #[\Override]

    protected function setUp(): void
    {

        $this->ogm = new OGM();
    }

    public function testConvertDate(): void
    {
        $data = [
            '$type'  => 'Date',
            '_value' => '2023-03-20'
        ];

        $result = $this->ogm->map($data);

        $this->assertInstanceOf(Date::class, $result, "The result should be an instance of Date");

        $dateTime = new \DateTimeImmutable('2023-03-20');
        $expectedDays = (int) floor($dateTime->getTimestamp() / 86400);

        $this->assertEquals($expectedDays, $result->getDays(), "The calculated days should match the expected value.");
    }
}
