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

        $mockUsername = 'mockUser';
        $mockPassword = 'mockPass';

        putenv('NEO4J_USERNAME=' . $mockUsername);
        putenv('NEO4J_PASSWORD=' . $mockPassword);


        $auth = Authentication::basic(getenv('NEO4J_USERNAME'), getenv('NEO4J_PASSWORD'));


        $expectedHeader = 'Basic ' . base64_encode("$mockUsername:$mockPassword");
        $this->assertEquals($expectedHeader, $auth->getHeader(), 'Basic authentication header mismatch.');
        $this->assertEquals('Basic', $auth->getType(), 'Type should be Basic.');

        putenv('NEO4J_USERNAME');
        putenv('NEO4J_PASSWORD');
    }

    public function testFallbackToEnvironmentVariables(): void
    {
        putenv('NEO4J_USERNAME=mockEnvUser');
        putenv('NEO4J_PASSWORD=mockEnvPass');

        $auth = Authentication::basic(getenv('NEO4J_USERNAME'), getenv('NEO4J_PASSWORD'));

        $expectedHeader = 'Basic ' . base64_encode("mockEnvUser:mockEnvPass");
        $this->assertEquals($expectedHeader, $auth->getHeader(), 'Basic authentication with environment variables mismatch.');
        $this->assertEquals('Basic', $auth->getType(), 'Type should be Basic.');

        putenv('NEO4J_USERNAME');
        putenv('NEO4J_PASSWORD');
    }
}