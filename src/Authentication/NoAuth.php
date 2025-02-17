<?php

namespace Neo4j\QueryAPI\Authentication;

use Psr\Http\Message\RequestInterface;

/**
 *  @api
 */
class NoAuth implements AuthenticateInterface
{
    public function getHeader(): string
    {
        return '';
    }

    public function getType(): string
    {
        return 'NoAuth';
    }
    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request;
    }
}
