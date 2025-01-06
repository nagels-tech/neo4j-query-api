<?php

use Neo4j\QueryAPI\Profile;

require __DIR__ . '/../vendor/autoload.php';

$neo4jUrl = '***REMOVED***/db/neo4j/query/v2';
$username = 'neo4j';
$password = '***REMOVED***';

$client = new Profile($neo4jUrl, $username, $password);

$params = ['name' => 'Alice'];

$query = "PROFILE MATCH (n:Person {name: \$name}) RETURN n.name";

$data = $client->executeQuery($query, $params);


$formattedResponse = $client->formatResponse($data);

echo json_encode($formattedResponse, JSON_PRETTY_PRINT);
