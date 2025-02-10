<?php

namespace Neo4j\QueryAPI\Objects;

use Exception;
use Neo4j\QueryAPI\AuthenticateInterface;
use Neo4j\QueryAPI\BasicAuthentication;
use Neo4j\QueryAPI\BearerAuthentication;
use Neo4j\QueryAPI\NoAuth;

class Authentication
{
    public static function basic(string $username, string $password): AuthenticateInterface
    {
        return new BasicAuthentication($username, $password);
    }

    public static function fromEnvironment(): AuthenticateInterface
    {
        // Fetch credentials from environment variables
        $username = getenv("NEO4J_USERNAME") ?: '';
        $password = getenv("NEO4J_PASSWORD") ?: '';

        return new BasicAuthentication($username, $password);
    }



    public static function noAuth(): AuthenticateInterface
    {
        return new NoAuth();
    }

    public static function bearer(string $token): AuthenticateInterface
    {
        return new BearerAuthentication($token);
    }
}
