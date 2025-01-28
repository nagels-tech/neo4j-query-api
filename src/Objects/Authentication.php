<?php

namespace Neo4j\QueryAPI\Objects;

use Exception;
use Neo4j\QueryAPI\AuthenticateInterface;
use Neo4j\QueryAPI\BasicAuthentication;
use Neo4j\QueryAPI\BearerAuthentication;
use Neo4j\QueryAPI\NoAuth;

class Authentication
{
    public static function basic(): AuthenticateInterface
    {
        return new BasicAuthentication(getenv("NEO4J_USERNAME"), getenv("NEO4J_PASSWORD"));
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
