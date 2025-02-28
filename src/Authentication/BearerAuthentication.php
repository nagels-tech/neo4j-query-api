<?php

namespace Neo4j\QueryAPI\Authentication;

use Psr\Http\Message\RequestInterface;

final class BearerAuthentication implements AuthenticateInterface
{
    public function __construct(private string $token)
    {
    }

    #[\Override]
    public function authenticate(RequestInterface $request): RequestInterface
    {
        $authHeader = 'Bearer ' . $this->token;
        return $request->withHeader('Authorization', $authHeader);
    }


    #[\Override]
    public function getHeader(): string
    {
        return 'Bearer ' . $this->token;
    }


    #[\Override]
    public function getType(): string
    {
        return 'Bearer';
    }
}
