<?php

namespace Neo4j\QueryAPI;

use Psr\Http\Message\RequestInterface;

class BearerAuthentication implements AuthenticateInterface
{
    public function __construct(private string $token)
    {
    }

    public function authenticate(RequestInterface $request): RequestInterface
    {
        $authHeader = 'Bearer ' . $this->token;
        return $request->withHeader('Authorization', $authHeader);
    }
}
