<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use Neo4j\QueryAPI\Tests\CreatesQueryAPI;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Enums\AccessMode;
use Neo4j\QueryAPI\Exception\Neo4jException;

class AccessModesIntegrationTest extends TestCase
{
    use CreatesQueryAPI;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createQueryAPI();
    }

    #[DoesNotPerformAssertions]
    public function testRunWithWriteAccessMode(): void
    {
        $this->api->run("CREATE (n:Person {name: 'Alice'}) RETURN n");
    }

    #[DoesNotPerformAssertions]
    public function testRunWithReadAccessMode(): void
    {
        $this->createQueryAPI(AccessMode::READ);
        $this->api->run("MATCH (n) RETURN COUNT(n)");
    }

    public function testReadModeWithWriteQuery(): void
    {
        $this->createQueryAPI(AccessMode::READ);
        $this->expectException(Neo4jException::class);
        $this->api->run("CREATE (n:Test {name: 'Test Node'})");
    }

    #[DoesNotPerformAssertions]
    public function testWriteModeWithReadQuery(): void
    {
        $this->api->run("MATCH (n:Test) RETURN n");
    }
}
