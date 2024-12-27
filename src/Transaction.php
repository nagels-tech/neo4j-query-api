<?php

namespace Neo4j\QueryAPI;

use Neo4j\QueryAPI\Results\ResultSet;

class Transaction
{
    public function run(string $statement, array $params = []): ResultSet
    {

    }

    public function commit(string $statement = null, array $parameters = []): null|ResultSet
    {

    }

    public function rollback(): void
    {

    }
}