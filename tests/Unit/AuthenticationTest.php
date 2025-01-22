<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Objects\Authentication;
use PHPUnit\Framework\TestCase;


class AuthenticationTest extends TestCase
{
    public function testBearerToken(): void
    {

        $mockToken = 'mocked_bearer_token';

        $auth = Authentication::request(token: $mockToken);

        $this->assertEquals("Bearer $mockToken", $auth->getHeader(), 'Bearer token mismatch.');
        $this->assertEquals('Bearer', $auth->getType(), 'Type should be Bearer.');
    }

    public function testBasicAuthentication(): void
    {

        $mockUsername = 'mockUser';
        $mockPassword = 'mockPass';
        $auth = Authentication::request($mockUsername, $mockPassword);

        $expectedHeader = 'Basic ' . base64_encode("$mockUsername:$mockPassword");
        $this->assertEquals($expectedHeader, $auth->getHeader(), 'Basic authentication header mismatch.');
        $this->assertEquals('Basic', $auth->getType(), 'Type should be Basic.');
    }

    public function testFallbackToEnvironmentVariables(): void
    {

        putenv('NEO4J_USERNAME=mockEnvUser');
        putenv('NEO4J_PASSWORD=mockEnvPass');

        $auth = Authentication::request();

        $expectedHeader = 'Basic ' . base64_encode("mockEnvUser:mockEnvPass");
        $this->assertEquals($expectedHeader, $auth->getHeader(), 'Basic authentication with environment variables mismatch.');
        $this->assertEquals('Basic', $auth->getType(), 'Type should be Basic.');

        putenv('NEO4J_USERNAME');
        putenv('NEO4J_PASSWORD');
    }

}
