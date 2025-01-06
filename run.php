<?php

namespace Neo4j\QueryAPI;
require './vendor/autoload.php'; // Make sure Guzzle is installed (composer require guzzlehttp/guzzle)

use GuzzleHttp\Client;


$neo4j_url = 'https://6f72daa1.databases.neo4j.io/';
$username  = 'neo4j';
//$password = 'your_password';
$password  = '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0';

$query = 'CREATE (n:Person {name: "Bobby"}) RETURN n';

$auth = base64_encode("$username:$password");

$payload = json_encode([
    'statement' => $query,
]);

$headers = [
    'Authorization' => 'Basic ' . $auth,
    'Content-Type'  => 'application/json',
    'Accept'        => 'application/json',
];


$client = new Client();

$response = $client->post('https://6f72daa1.databases.neo4j.io/db/neo4j/query/v2/tx', [
    'headers' => $headers,
    'body'    => $payload,
]);
$responseData = json_decode($response->getBody(), true);

print_r($responseData);