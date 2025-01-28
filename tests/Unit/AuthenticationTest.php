<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Objects\Authentication;
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    public function testBearerToken(): void
    {
        $mockToken = 'mocked_bearer_token';
        $auth = Authentication::bearer($mockToken);
        $this->assertEquals("Bearer $mockToken", $auth->getHeader());
        $this->assertEquals('Bearer', $auth->getType());
    }

    public function testBasicAuthentication(): void
    {
        $mockUsername = 'mockUser';
        $mockPassword = 'mockPass';
        putenv('NEO4J_USERNAME=' . $mockUsername);
        putenv('NEO4J_PASSWORD=' . $mockPassword);
        $auth = Authentication::basic();
        $expectedHeader = 'Basic ' . base64_encode("$mockUsername:$mockPassword");
        $this->assertEquals($expectedHeader, $auth->getHeader());
        $this->assertEquals('Basic', $auth->getType());
        putenv('NEO4J_USERNAME');
        putenv('NEO4J_PASSWORD');
    }

    public function testFallbackToEnvironmentVariables(): void
    {
        putenv('NEO4J_USERNAME=mockEnvUser');
        putenv('NEO4J_PASSWORD=mockEnvPass');
        $auth = Authentication::basic();
        $expectedHeader = 'Basic ' . base64_encode("mockEnvUser:mockEnvPass");
        $this->assertEquals($expectedHeader, $auth->getHeader());
        $this->assertEquals('Basic', $auth->getType());
        putenv('NEO4J_USERNAME');
        putenv('NEO4J_PASSWORD');
    }

    public function testNoAuth(): void
    {
        $auth = Authentication::noAuth();

        $this->assertNull($auth->getHeader());

        $this->assertEquals('NoAuth', $auth->getType());
    }

}
