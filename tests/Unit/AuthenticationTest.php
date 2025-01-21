<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Objects\Authentication;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class AuthenticationTest extends TestCase
{
    public function testBearerToken(): void
    {
        // Mock a bearer token
        $mockToken = 'mocked_bearer_token';

        // Create an Authentication instance with the bearer token
        $auth = Authentication::request(token: $mockToken);

        // Assert that the Authorization header is correct
        $this->assertEquals("Bearer $mockToken", $auth->getHeader(), 'Bearer token mismatch.');
        $this->assertEquals('Bearer', $auth->getType(), 'Type should be Bearer.');
    }

    public function testBasicAuthentication(): void
    {
        // Mock username and password
        $mockUsername = 'mockUser';
        $mockPassword = 'mockPass';

        // Create an Authentication instance with username and password
        $auth = Authentication::request($mockUsername, $mockPassword);

        // Assert that the Authorization header is correct
        $expectedHeader = 'Basic ' . base64_encode("$mockUsername:$mockPassword");
        $this->assertEquals($expectedHeader, $auth->getHeader(), 'Basic authentication header mismatch.');
        $this->assertEquals('Basic', $auth->getType(), 'Type should be Basic.');
    }

    public function testFallbackToEnvironmentVariables(): void
    {
        // Mock environment variables
        putenv('NEO4J_USERNAME=mockEnvUser');
        putenv('NEO4J_PASSWORD=mockEnvPass');

        // Create an Authentication instance with environment variables
        $auth = Authentication::request();

        // Assert that the Authorization header is correct
        $expectedHeader = 'Basic ' . base64_encode("mockEnvUser:mockEnvPass");
        $this->assertEquals($expectedHeader, $auth->getHeader(), 'Basic authentication with environment variables mismatch.');
        $this->assertEquals('Basic', $auth->getType(), 'Type should be Basic.');

        // Cleanup the environment variables
        putenv('NEO4J_USERNAME');
        putenv('NEO4J_PASSWORD');
    }

}
