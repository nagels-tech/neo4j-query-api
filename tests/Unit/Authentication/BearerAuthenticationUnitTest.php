<?php

namespace Neo4j\QueryAPI\Tests\Unit\Authentication;

use Neo4j\QueryAPI\Authentication\BearerAuthentication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class BearerAuthenticationUnitTest extends TestCase
{
    private BearerAuthentication $auth;
    private RequestInterface&MockObject $requestMock;
    #[\Override]
    protected function setUp(): void
    {
        $this->auth = new BearerAuthentication('testToken');
        $this->requestMock = $this->createMock(RequestInterface::class);
    }

    public function testAuthenticateAddsAuthorizationHeader(): void
    {
        $authHeader = 'Bearer testToken';

        $this->requestMock->expects($this->once())
            ->method('withHeader')
            ->with('Authorization', $authHeader)
            ->willReturnSelf();

        $result = $this->auth->authenticate($this->requestMock);
        $this->assertSame($this->requestMock, $result);
    }

    public function testGetHeaderReturnsCorrectValue(): void
    {
        $expectedHeader = 'Bearer testToken';
        $this->assertEquals($expectedHeader, $this->auth->getHeader());
    }

    public function testGetTypeReturnsBearer(): void
    {
        $this->assertEquals('Bearer', $this->auth->getType());
    }
}
