<?php

namespace Neo4j\QueryAPI\Objects;

use Neo4j\QueryAPI\Authentication\AuthenticateInterface;
use Neo4j\QueryAPI\Authentication\BasicAuthentication;
use Neo4j\QueryAPI\Authentication\BearerAuthentication;
use Neo4j\QueryAPI\Authentication\NoAuth;

/**
 *  @api
 */
class Authentication
{

    public static function basic(string $username, string $password): AuthenticateInterface
    {
        $username = $username ?: 'defaultUsername';
        $password = $password ?: 'defaultPassword';

        return new BasicAuthentication($username, $password);
    }


    public static function fromEnvironment(): AuthenticateInterface
    {
        $username = getenv("NEO4J_USERNAME");
        $password = getenv("NEO4J_PASSWORD");

        return new BasicAuthentication(
            $username !== false ? $username : null,
            $password !== false ? $password : null
        );
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
