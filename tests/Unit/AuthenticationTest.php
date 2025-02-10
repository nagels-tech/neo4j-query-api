<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Neo4j\QueryAPI\BasicAuthentication;
use Neo4j\QueryAPI\Objects\Authentication;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

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

    /**
     * @throws Exception
     */
    public function testBasicAuthentication(): void
    {
        $mockUsername = 'neo4j';
        $mockPassword = '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0';


        $auth = Authentication::basic($mockUsername, $mockPassword);

        $this->assertInstanceOf(BasicAuthentication::class, $auth);

        $expectedHeader = 'Basic ' . base64_encode("$mockUsername:$mockPassword");

        $request = $this->createMock(RequestInterface::class);

        $request->expects($this->once())
            ->method('withHeader')
            ->with('Authorization', $expectedHeader)  // Use dynamically generated expected header
            ->willReturn($request);
   }
}
