<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Objects\Authentication;
use PHPUnit\Framework\TestCase;

/**
 *  @api
 */
final class AuthenticationTest extends TestCase
{
    public function testBearerToken(): void
    {
        $mockToken = 'mocked_bearer_token';


        $auth = Authentication::bearer($mockToken);

        $this->assertEquals("Bearer $mockToken", $auth->getHeader(), 'Bearer token mismatch.');
        $this->assertEquals('Bearer', $auth->getType(), 'Type should be Bearer.');
    }

    public function testBasicAuthentication(): void
    {
        $mockUsername = 'mockUser';
        $mockPassword = 'mockPass';

        putenv('NEO4J_USERNAME=' . $mockUsername);
        putenv('NEO4J_PASSWORD=' . $mockPassword);

        $username = getenv('NEO4J_USERNAME');
        $password = getenv('NEO4J_PASSWORD');

        $username = is_string($username) ? $username : 'defaultUser';
        $password = is_string($password) ? $password : 'defaultPass';

        $auth = Authentication::basic($username, $password);

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

        $username = getenv('NEO4J_USERNAME');
        $password = getenv('NEO4J_PASSWORD');

        $username = is_string($username) ? $username : 'fallbackUser';
        $password = is_string($password) ? $password : 'fallbackPass';

        $auth = Authentication::basic($username, $password);

        $expectedHeader = 'Basic ' . base64_encode("mockEnvUser:mockEnvPass");
        $this->assertEquals($expectedHeader, $auth->getHeader(), 'Basic authentication with environment variables mismatch.');
        $this->assertEquals('Basic', $auth->getType(), 'Type should be Basic.');

        putenv('NEO4J_USERNAME');
        putenv('NEO4J_PASSWORD');
    }

}
