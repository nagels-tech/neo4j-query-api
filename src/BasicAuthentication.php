<?php

namespace Neo4j\QueryAPI;

use Psr\Http\Message\RequestInterface;

class BasicAuthentication implements AuthenticateInterface
{
    private string $username;
    private string $password;

    public function __construct(?string $username = null, ?string $password = null)
    {
        $this->username = $username ?? getenv("NEO4J_USERNAME") ?: '';
        $this->password = $password ?? getenv("NEO4J_PASSWORD") ?: '';
    }

    public function authenticate(RequestInterface $request): RequestInterface
    {
        $authHeader = $this->getHeader();
        return $request->withHeader('Authorization', $authHeader);
    }

    public function getHeader(): string
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }

    public function getType(): string
    {
        return 'Basic';
    }
}
