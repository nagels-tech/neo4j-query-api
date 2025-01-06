<?php

require __DIR__ . '/../vendor/autoload.php';

use Neo4j\QueryAPI\Neo4jQueryAPI;


$neo4jUrl = 'https://6f72daa1.databases.neo4j.io/';
$username = 'neo4j';
$password = '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0';

$api = Neo4jQueryAPI::login($neo4jUrl, $username, $password);

$transaction = $api->beginTransaction();

$query = 'CREATE (n:Person {name: "Bobby"}) RETURN n';

$response = $transaction->run($query);

print_r($response);

$transaction->commit();

