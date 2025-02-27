<?php

namespace Neo4j\QueryAPI\Tests\Unit\Authentication;

use DG\BypassFinals;
use Neo4j\QueryAPI\Authentication\NoAuth;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class NoAuthUnitTest extends TestCase
{
    private NoAuth $auth;
    private RequestInterface $requestMock;

    #[\Override]
    protected function setUp(): void
    {
        BypassFinals::enable();

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
