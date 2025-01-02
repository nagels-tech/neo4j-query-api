<?php

use Neo4j\QueryAPI\Transaction;
use GuzzleHttp\Exception\RequestException;

require __DIR__ . '/../vendor/autoload.php';

$api = Transaction::login(
    'https://bb79fe35.databases.neo4j.io',
    'neo4j',
    'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0'
);

$query = 'MATCH (n:Person) RETURN n.name LIMIT 10';
$results = $api->run($query, []);

// Display the results
echo "<pre>";
print_r($results);
echo "</pre>";


