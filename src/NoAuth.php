<?php

namespace Neo4j\QueryAPI;

use Psr\Http\Message\RequestInterface;

class NoAuth implements AuthenticateInterface
{
    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request; // No changes to the request as there is no authentication.
    }

    public function getHeader(): ?string
    {
        return null; // No authentication header for NoAuth
    }

    public function getType(): string
    {
        return 'NoAuth'; // Indicating that no authentication is used
    }
}
