<?php

use Neo4j\QueryAPI\Neo4jQueryAPI;

require __DIR__ . '/../vendor/autoload.php';

$api = Neo4jQueryAPI::login('https://bb79fe35.databases.neo4j.io', 'neo4j', 'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0');

// Run the query and fetch results
$results = $api->run('MATCH (n:Person) RETURN n LIMIT 10');

echo "<pre>";
print_r($results);
echo "</pre>";