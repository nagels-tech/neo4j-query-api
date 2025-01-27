<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\Objects\Authentication;
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    /*  public function testBearerToken(): void
      {
          $mockToken = 'mocked_bearer_token';

          // Create the Bearer Authentication object
          $auth = Authentication::bearer($mockToken);

          // Assuming BearerAuthentication returns a valid header array
          $header = $auth->getHeader('Authorization');
          $this->assertEquals(['Authorization' => "Bearer $mockToken"], $header, 'Bearer token mismatch.');
      }

      public function testBasicAuthentication(): void
      {
          $mockUsername = 'mockUser';
          $mockPassword = 'mockPass';

          // Create the Basic Authentication object
          $auth = Authentication::basic($mockUsername, $mockPassword);

          // Expected header should be the Basic Authentication header with base64 encoding
          $expectedHeader = ['Authorization' => 'Basic ' . base64_encode("$mockUsername:$mockPassword")];
          $header = $auth->getHeader('Authorization');

          $this->assertEquals($expectedHeader, $header, 'Basic authentication header mismatch.');
      }

      public function testFallbackToEnvironmentVariables(): void
      {
          // Set environment variables for fallback
          putenv('NEO4J_USERNAME=mockEnvUser');
          putenv('NEO4J_PASSWORD=mockEnvPass');

          // Create the Basic Authentication object using environment variables
          $auth = Authentication::basic('', '');

          // Expected header should be based on the environment variables
          $expectedHeader = ['Authorization' => 'Basic ' . base64_encode('mockEnvUser:mockEnvPass')];
          $header = $auth->getHeader('Authorization');

          $this->assertEquals($expectedHeader, $header, 'Basic authentication with environment variables mismatch.');

          // Clean up the environment variables
          putenv('NEO4J_USERNAME');
          putenv('NEO4J_PASSWORD');
      }*/

    public function testNoAuthAuthentication(): void
    {
        // Create the NoAuth Authentication object
        $auth = Authentication::noAuth();

        // Expected header should be empty as there is no authentication
        $header = $auth->getHeader('Authorization');
        $this->assertEmpty($header, 'NoAuth should not have an Authorization header.');
    }
}
