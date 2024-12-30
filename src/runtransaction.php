<?php

require __DIR__ . '/../vendor/autoload.php';

use Neo4j\QueryAPI\Neo4jQueryAPI;


$neo4jUrl = '***REMOVED***/';
$username = 'neo4j';
$password = '***REMOVED***';

$api = Neo4jQueryAPI::login($neo4jUrl, $username, $password);

$transaction = $api->beginTransaction();

$query = 'CREATE (n:Person {name: "Bobby"}) RETURN n';

$response = $transaction->run($query);

print_r($response);

$transaction->commit();

