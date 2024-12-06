<?php

use Neo4j\QueryAPI\Neo4jQueryAPI;
use GuzzleHttp\Exception\RequestException;

require __DIR__ . '/../vendor/autoload.php';


// Login to the Neo4j instance
$api = Neo4jQueryAPI::login(
    'https://bb79fe35.databases.neo4j.io',
    'neo4j',
    'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0'
);

// Run a query to fetch results
$query = 'MATCH (n:Person) RETURN n.name LIMIT 10';
$results = $api->run($query, []);

// Display the results
echo "<pre>";
print_r($results);
echo "</pre>";


