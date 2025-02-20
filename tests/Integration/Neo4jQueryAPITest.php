<?php

namespace Neo4j\QueryAPI\Tests\Integration;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Configuration;

/** @psalm-suppress UnusedClass */
class Neo4jQueryAPITest extends TestCase
{

    public function testLoginWithValidConfiguration()
    {
        $config = new Configuration(baseUri: 'http://valid.address');

        $api = Neo4jQueryAPI::login('http://myaddress', Authentication::fromEnvironment(), $config);

        $this->assertInstanceOf(Neo4jQueryAPI::class, $api);
        $this->assertEquals('http://valid.address', $api->getConfig()->baseUri);
    }

    public function testLoginWithNullConfiguration()
    {
        $config = null;

        $api = Neo4jQueryAPI::login('http://myaddress', Authentication::fromEnvironment(), $config);

        $this->assertInstanceOf(Neo4jQueryAPI::class, $api);
        $this->assertEquals('http://myaddress', $api->getConfig()->baseUri);
    }

    public function testConfigOnly()
    {
        $config = new Configuration(baseUri: 'http://valid.address');

        $api = Neo4jQueryAPI::login(auth: Authentication::fromEnvironment(), config: $config);

        $this->assertInstanceOf(Neo4jQueryAPI::class, $api);
        $this->assertEquals('http://valid.address', $api->getConfig()->baseUri);
    }
}
