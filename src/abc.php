<?php

require __DIR__ . '/../vendor/autoload.php';

use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Authentication\AuthenticateInterface;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Configuration;
use Neo4j\QueryAPI\Results\ResultSet;

$neo4jUrl = 'bb79fe35.databases.neo4j.io';  // Your Neo4j Aura instance URL (without `neo4j+s://`)
$neo4jUsername = 'neo4j';  // Your Neo4j username
$neo4jPassword = 'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0';  // Your Neo4j password

try {
    // Correctly instantiate the $auth object with basic authentication
    $auth = Authentication::basic($neo4jUsername, $neo4jPassword);

    // Call the login method with your custom library (using Bolt protocol)
    $neo4j = Neo4jQueryAPI::login(
        $neo4jUrl,  // URL without `neo4j+s://`
        $auth
    );

    // Run a simple Cypher query
    $cypher = 'MATCH (n) RETURN n LIMIT 10';
    $resultSet = $neo4j->run($cypher);

    // Check the result structure (debugging purposes)
    var_dump($resultSet);  // To inspect the response data

    // Handle results
    foreach ($resultSet as $row) {
        echo "Node: " . json_encode($row) . "\n";
    }

    // Optionally, start a transaction
    $transaction = $neo4j->beginTransaction();

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
