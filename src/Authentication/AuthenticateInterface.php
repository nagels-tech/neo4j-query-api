<?php

namespace Neo4j\QueryAPI\Authentication;

use Psr\Http\Message\RequestInterface;

/**
 *  @api
 */
interface AuthenticateInterface
{
    /**
     * Authenticates the request by returning a new instance of the request with the authentication information attached.
     */
    public function authenticate(RequestInterface $request): RequestInterface;
}
