<?php

namespace Neo4j\QueryAPI\Tests\Unit\objects;

use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Authentication\BasicAuthentication;
use Neo4j\QueryAPI\Authentication\BearerAuthentication;
use Neo4j\QueryAPI\Authentication\NoAuth;
use PHPUnit\Framework\TestCase;

final class AuthenticationUnitTest extends TestCase
{
    public function testBasicReturnsBasicAuthenticationInstance(): void
    {
        $auth = Authentication::basic('testUser', 'testPass');
        $this->assertInstanceOf(BasicAuthentication::class, $auth);
    }

    public function testFromEnvironmentReturnsBasicAuthenticationInstance(): void
    {
        putenv('NEO4J_USERNAME=testUser');
        putenv('NEO4J_PASSWORD=testPass');

        $auth = Authentication::fromEnvironment();
        $this->assertInstanceOf(BasicAuthentication::class, $auth);
    }

    public function testNoAuthReturnsNoAuthInstance(): void
    {
        $auth = Authentication::noAuth();
        $this->assertInstanceOf(NoAuth::class, $auth);
    }

    public function testBearerReturnsBearerAuthenticationInstance(): void
    {
        $auth = Authentication::bearer('testToken');
        $this->assertInstanceOf(BearerAuthentication::class, $auth);
    }
}
