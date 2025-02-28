<?php

namespace Neo4j\QueryAPI\Authentication;

use Psr\Http\Message\RequestInterface;

final class BasicAuthentication implements AuthenticateInterface
{
    private string $username;
    private string $password;

    public function __construct(?string $username = null, ?string $password = null)
    {
        $this->username = $username ?? (is_string($envUser = getenv("NEO4J_USERNAME")) ? $envUser : '');
        $this->password = $password ?? (is_string($envPass = getenv("NEO4J_PASSWORD")) ? $envPass : '');
    }


    #[\Override]
    public function authenticate(RequestInterface $request): RequestInterface
    {
        $authHeader = $this->getHeader();
        return $request->withHeader('Authorization', $authHeader);
    }

    #[\Override]
    public function getHeader(): string
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }
    /**
     * @psalm-suppress UnusedMethod
     */
    #[\Override]
    public function getType(): string
    {
        return 'Basic';
    }
}
