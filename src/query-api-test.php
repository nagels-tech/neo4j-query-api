<?php

use Neo4j\QueryAPI\Neo4jQueryAPI;
use GuzzleHttp\Exception\RequestException;

require __DIR__ . '/../vendor/autoload.php';


// Login to the Neo4j instance
$api = Neo4jQueryAPI::login(
    'https://f2455ee6.databases.neo4j.io',
    'neo4j',
    'h5YLhuoSnPD6yMy8OwmFPXs6WkL8uX25zxHCKhiF_hY'
);

// Run a query to fetch results
$query = 'MATCH (n:Person) RETURN n.name LIMIT 10';
$results = $api->run($query, []);

// Display the results
echo "<pre>";
print_r($results);
echo "</pre>";


