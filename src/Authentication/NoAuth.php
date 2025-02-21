<?php

namespace Neo4j\QueryAPI\Authentication;

use Psr\Http\Message\RequestInterface;

/**
 *  @api
 */
class NoAuth implements AuthenticateInterface
{
    #[\Override]
    public function getHeader(): string
    {
        return '';
    }

    #[\Override]
    public function getType(): string
    {
        return 'NoAuth';
    }
    #[\Override]
    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request;
    }
}
