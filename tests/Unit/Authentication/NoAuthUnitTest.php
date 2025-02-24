<?php

namespace Neo4j\QueryAPI\Tests\Unit\Authentication;

use Neo4j\QueryAPI\Authentication\NoAuth;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class NoAuthUnitTest extends TestCase
{
    private NoAuth $auth;
    private RequestInterface $requestMock;

    protected function setUp(): void
    {
        $this->auth = new NoAuth();
        $this->requestMock = $this->createMock(RequestInterface::class);
    }

    public function testAuthenticateReturnsUnmodifiedRequest(): void
    {

        $this->assertSame($this->requestMock, $this->auth->authenticate($this->requestMock));
    }

    public function testGetHeaderReturnsEmptyString(): void
    {
        $this->assertEquals('', $this->auth->getHeader());
    }

    public function testGetTypeReturnsNoAuth(): void
    {
        $this->assertEquals('NoAuth', $this->auth->getType());
    }
}
