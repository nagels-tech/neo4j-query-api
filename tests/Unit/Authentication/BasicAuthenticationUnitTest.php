<?php

namespace Neo4j\QueryAPI\Tests\Unit\Authentication;

use Neo4j\QueryAPI\Authentication\BasicAuthentication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class BasicAuthenticationUnitTest extends TestCase
{
    private BasicAuthentication $auth;
    private MockObject&RequestInterface $requestMock;


    #[\Override]
    protected function setUp(): void
    {
        $this->auth = new BasicAuthentication('testUser', 'testPass');
        $this->requestMock = $this->createMock(RequestInterface::class);
    }

    public function testAuthenticateAddsAuthorizationHeader(): void
    {
        $authHeader = 'Basic ' . base64_encode('testUser:testPass');

        $this->requestMock->expects($this->once())
            ->method('withHeader')
            ->with('Authorization', $authHeader)
            ->willReturn($this->requestMock);

        $result = $this->auth->authenticate($this->requestMock);
        $this->assertSame($this->requestMock, $result);
    }

    public function testGetHeaderReturnsCorrectValue(): void
    {
        $expectedHeader = 'Basic ' . base64_encode('testUser:testPass');
        $this->assertEquals($expectedHeader, $this->auth->getHeader());
    }

    public function testGetTypeReturnsBasic(): void
    {
        $this->assertEquals('Basic', $this->auth->getType());
    }
}
