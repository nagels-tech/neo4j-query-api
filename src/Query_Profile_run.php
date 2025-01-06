<?php

use Neo4j\QueryAPI\Profile;

require __DIR__ . '/../vendor/autoload.php';

$neo4jUrl = 'https://6f72daa1.databases.neo4j.io/db/neo4j/query/v2';
$username = 'neo4j';
$password = '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0';

$client = new Profile($neo4jUrl, $username, $password);

$params = ['name' => 'Alice'];

$query = "PROFILE MATCH (n:Person {name: \$name}) RETURN n.name";

$data = $client->executeQuery($query, $params);


$formattedResponse = $client->formatResponse($data);

echo json_encode($formattedResponse, JSON_PRETTY_PRINT);
