<?php

require '../vendor/autoload.php';
use GuzzleHttp\Client;
use Neo4j\QueryAPI\Objects\ResultCounters;

$username = 'neo4j';
$password = '***REMOVED***';
$connectionUrl = '***REMOVED***/db/neo4j/query/v2';

$auth = base64_encode("$username:$password");

$client = new Client();

$resultCounters = new ResultCounters(
    containsUpdates: false,
    nodesCreated: 0,
    nodesDeleted: 0,
    propertiesSet: 0,
    relationshipsCreated: 0,
    relationshipsDeleted: 0,
    labelsAdded: 0,
    labelsRemoved: 0,
    indexesAdded: 0,
    indexesRemoved: 0,
    constraintsAdded: 0,
    constraintsRemoved: 0,
    containsSystemUpdates: false,
    systemUpdates: 0
);

$response = $client->post($connectionUrl, [
    'json' => [
        'statement' => 'CREATE (n:Person {name: $name}) RETURN n.name AS name',
        'parameters' => [
            'name' => 'Alice'
        ],
        'includeCounters' => true,
        'bookmarks' => $resultCounters->getBookmarks(),
    ],
    'headers' => [
        'Authorization' => 'Basic ' . $auth,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],
]);

$data = json_decode($response->getBody()->getContents(), true);

foreach ($data['counters'] as $key => $value) {
    $resultCounters->setCounter($key, $value);
}

if (isset($data['bookmark'])) {
    $resultCounters->addBookmark($data['bookmark']);
}
