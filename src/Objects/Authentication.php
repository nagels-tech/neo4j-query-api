<?php

namespace Neo4j\QueryAPI\Objects;

use InvalidArgumentException;

class Authentication
{
    private string $header;
    private string $type;

    private function __construct(string $header, string $type)
    {
        $this->header = $header;
        $this->type = $type;
    }

    public static function request(string $username = null, string $password = null, string $token = null): self
    {
        if ($token !== null) {
            return self::bearer($token);
        }
        if ($username === null) {
            $username = getenv('NEO4J_USERNAME');
        }

        if ($password === null) {
            $password = getenv('NEO4J_PASSWORD');
        }
        if ($username !== null && $password !== null) {
            return self::basic($username, $password);
        }

        throw new InvalidArgumentException("Both username and password cannot be null.");
    }

    private static function basic(string $username, string $password): self
    {
        return new self("Basic " . base64_encode("$username:$password"), 'Basic');
    }

    private static function bearer(string $token): self
    {
        return new self("Bearer $token", 'Bearer');
    }

    public function getHeader(): string
    {
        return $this->header; // Return the header string directly
    }

    public function getType(): string
    {
        return $this->type;
    }
}
