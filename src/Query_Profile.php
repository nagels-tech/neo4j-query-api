<?php
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$neo4jUrl = '***REMOVED***/db/neo4j/query/v2';
$username = 'neo4j';
$password = '***REMOVED***';

$client = new Client();

$query = "PROFILE MATCH (n:Person) RETURN n";

$response = $client->post($neo4jUrl, [
    'auth' => [$username, $password],
    'json' => [
        'statement' => $query
    ]
]);

$body = $response->getBody();
$data = json_decode($body, true);

$output = [
    "data" => [
        "fields" => [],
        "values" => []
    ],
    "profiledQueryPlan" => [],
    "bookmarks" => $data['bookmarks'] ?? []
];

if (isset($data['result']['columns']) && isset($data['result']['rows'])) {
    $output["data"]["fields"] = $data['result']['columns'];
    foreach ($data['result']['rows'] as $row) {
        $output["data"]["values"][] = $row;
    }
}

if (isset($data['profiledQueryPlan'])) {
    $output["profiledQueryPlan"] = $data['profiledQueryPlan'];
}

echo json_encode($output, JSON_PRETTY_PRINT);
