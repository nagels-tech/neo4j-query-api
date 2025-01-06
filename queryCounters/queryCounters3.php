<?php


//http based approach:
//HTTP requests to Neo4j's REST API or Cypher HTTP endpoints, and dynamically fetch query statistics.
//sending a Cypher query through a POST request to Neo4j's HTTP API endpoint.

require '../vendor/autoload.php';
use GuzzleHttp\Client;
use Neo4j\QueryAPI\Objects\ResultCounters;

$username  = 'neo4j';
$password  = '***REMOVED***';
$impersonatedUser = 'app_user';
$query = 'CREATE (n:Person {name: "Bobby"}) RETURN n';

$auth = base64_encode("$username:$password");

$client = new Client();
$response = $client->post('***REMOVED***/db/neo4j/query/v2', [
    'json' => [
                'statement' => "CREATE (n:Person {name: 'Peter'}) RETURN n.name AS name",
                'includeCounters' => true,

    ],
    'headers' => [
            'Authorization' => 'Basic ' . $auth,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Impersonate-User' => $impersonatedUser
        ]
]);


$data = json_decode($response->getBody()->getContents(), true);



$counters = new \Neo4j\QueryAPI\Objects\ResultCounters(
    containsUpdates: $data['counters']['containsUpdates'],
    nodesCreated: $data['counters']['nodesCreated'],
    nodesDeleted: $data['counters']['nodesDeleted'],
    propertiesSet: $data['counters']['propertiesSet'],
    relationshipsCreated: $data['counters']['relationshipsCreated'],
    relationshipsDeleted: $data['counters']['relationshipsDeleted'],
    labelsAdded: $data['counters']['labelsAdded'],
    labelsRemoved: $data['counters']['labelsRemoved'],
    indexesAdded: $data['counters']['indexesAdded'],
    indexesRemoved: $data['counters']['indexesRemoved'],
    constraintsAdded: $data['counters']['constraintsAdded'],
    constraintsRemoved: $data['counters']['constraintsRemoved'],
    containsSystemUpdates: $data['counters']['containsSystemUpdates'],
    systemUpdates: $data['counters']['systemUpdates']
);



