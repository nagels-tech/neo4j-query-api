<?php

namespace Neo4j\QueryAPI\Tests\Unit\Authentication;

use Neo4j\QueryAPI\Authentication\NoAuth;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class NoAuthUnitTest extends TestCase
{
    public function testGetHeaderReturnsEmptyString()
    {
        $auth = new NoAuth();
        $this->assertSame('', $auth->getHeader());
    }

    public function testGetTypeReturnsNoAuth()
    {
        $auth = new NoAuth();
        $this->assertSame('NoAuth', $auth->getType());
    }

    public function testAuthenticateReturnsSameRequest()
    {

        $requestMock = $this->createMock(RequestInterface::class);

        $auth = new NoAuth();
        $result = $auth->authenticate($requestMock);

        $this->assertSame($requestMock, $result);
    }
}
