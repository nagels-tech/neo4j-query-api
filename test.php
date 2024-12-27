<?php

require 'vendor/autoload.php'; // Make sure Guzzle is installed (composer require guzzlehttp/guzzle)

use GuzzleHttp\Client;

//$neo4j_url = '***REMOVED***/db/neo4j/query/v2/tx';
//$neo4j_url = 'http://localhost:7474/db/neo4j/query/v2/tx';
$username  = 'neo4j';
$password = 'your_password';
//$password  = '***REMOVED***';

$query = 'CREATE (n:Person) RETURN n';

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

$response = $client->post('***REMOVED***/db/neo4j/query/v2/tx', [
    'headers' => $headers,
    'body'    => $payload,
]);
$clusterAffinity = $response->getHeaderLine('neo4j-cluster-affinity');
$responseData = json_decode($response->getBody(), true);
$headers['neo4j-cluster-affinity'] = $clusterAffinity;

$transactionId = $responseData['transaction']['id'];

$response = $client->delete('http://localhost:7474/db/neo4j/query/v2/tx/' . $transactionId, [
    'headers' => $headers,
    'body'    => $payload,
]);
$responseData = json_decode($response->getBody(), true);


//$commitUrl = '***REMOVED***/db/neo4j/tx/' . $responseData['transaction']['id'] . '/query/v2/commit';
$response = $client->post('http://localhost:7474/db/neo4j/query/v2/tx/' . $transactionId . '/commit', [
    'headers' => $headers,
    'body'    => $payload,
]);
$responseData = json_decode($response->getBody(), true);
print_r($responseData);

