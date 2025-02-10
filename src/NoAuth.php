<?php

namespace Neo4j\QueryAPI;

use Psr\Http\Message\RequestInterface;

class NoAuth implements AuthenticateInterface
{
    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request;
    }
}


