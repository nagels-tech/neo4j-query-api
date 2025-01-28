<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Objects\Authentication;
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    public function testBearerToken(): void
    {
        // Mock Bearer token
        $mockToken = 'mocked_bearer_token';

        // Use the Authentication::bearer method to get the Bearer authentication instance
        $auth = Authentication::bearer($mockToken);

        // Assert: Ensure correct header and type for Bearer token
        $this->assertEquals("Bearer $mockToken", $auth->getHeader(), 'Bearer token mismatch.');
        $this->assertEquals('Bearer', $auth->getType(), 'Type should be Bearer.');
    }

    public function testBasicAuthentication(): void
    {
        // Mocked username and password
        $mockUsername = 'mockUser';
        $mockPassword = 'mockPass';

        // Mock environment variables to return the mocked values
        putenv('NEO4J_USERNAME=' . $mockUsername);
        putenv('NEO4J_PASSWORD=' . $mockPassword);

        // Use Authentication::basic() to get the Basic authentication instance
        $auth = Authentication::basic();

        // Assert: Ensure correct Basic auth header is generated
        $expectedHeader = 'Basic ' . base64_encode("$mockUsername:$mockPassword");
        $this->assertEquals($expectedHeader, $auth->getHeader(), 'Basic authentication header mismatch.');
        $this->assertEquals('Basic', $auth->getType(), 'Type should be Basic.');

        // Clean up: Remove environment variables after the test
        putenv('NEO4J_USERNAME');
        putenv('NEO4J_PASSWORD');
    }

    public function testFallbackToEnvironmentVariables(): void
    {
        // Mock environment variables for Neo4j username and password
        putenv('NEO4J_USERNAME=mockEnvUser');
        putenv('NEO4J_PASSWORD=mockEnvPass');

        // Use Authentication::basic() to get the Basic authentication instance
        $auth = Authentication::basic();

        // Assert: Ensure that the correct Basic authentication header is generated
        $expectedHeader = 'Basic ' . base64_encode("mockEnvUser:mockEnvPass");
        $this->assertEquals($expectedHeader, $auth->getHeader(), 'Basic authentication with environment variables mismatch.');
        $this->assertEquals('Basic', $auth->getType(), 'Type should be Basic.');

        // Clean up environment variables
        putenv('NEO4J_USERNAME');
        putenv('NEO4J_PASSWORD');
    }
}
