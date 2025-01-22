<?php

namespace Neo4j\QueryAPI;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BasicAuthentication implements AuthenticateInterface
{
    public function __construct(private string $username, private string $password)
    {}

    public function authenticate(RequestInterface $request): RequestInterface
    {
        $authHeader = 'Basic ' . base64_encode($this->username . ':' . $this->password);
        return $request->withHeader('Authorization', $authHeader);
    }
}
