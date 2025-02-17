<?php

namespace Neo4j\QueryAPI\Authentication;

use Psr\Http\Message\RequestInterface;

/**
 *  @api
 */
class BearerAuthentication implements AuthenticateInterface
{
    public function __construct(private string $token)
    {
        $this->token = $token;
    }

    public function authenticate(RequestInterface $request): RequestInterface
    {
        $authHeader = 'Bearer ' . $this->token;
        return $request->withHeader('Authorization', $authHeader);
    }


    public function getHeader(): string
    {
        return 'Bearer ' . $this->token;
    }


    public function getType(): string
    {
        return 'Bearer';
    }
}
