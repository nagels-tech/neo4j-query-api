<?php

namespace Neo4j\QueryAPI;

use Psr\Http\Message\RequestInterface;

interface AuthenticateInterface extends RequestInterface
{
    /**
     * Authenticates the request by returning a new instance of the request with the authentication information attached.
     */
    public function authenticate(RequestInterface $request): RequestInterface;
}
