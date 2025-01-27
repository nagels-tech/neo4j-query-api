<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Ensure you include the autoloader for your dependencies

use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;

try {
    // Configuration
    $neo4jHost = 'https://6f72daa1.databases.neo4j.io/'; // Replace with your Neo4j host
    $username = 'neo4j'; // Replace with your Neo4j username
    $password = '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0'; // Replace with your Neo4j password
    $database = 'neo4j'; // Default database (replace if needed)

    // Authentication setup
    $auth = Authentication::basic($username, $password);

    // Initialize the Neo4jQueryAPI instance
    $neo4j = Neo4jQueryAPI::login($neo4jHost, $auth);

    // Run a sample Cypher query
    $cypherQuery = 'MATCH (n) RETURN n LIMIT 10'; // Replace with your desired query
    $parameters = []; // Optional query parameters

    $resultSet = $neo4j->run($cypherQuery, $parameters, $database);

    // Output the results
    echo "Query executed successfully!\n";
    foreach ($resultSet->getRows() as $row) {
        echo json_encode($row->toArray(), JSON_PRETTY_PRINT) . "\n";
    }

    // Output result counters
    $counters = $resultSet->getCounters();
    echo "Nodes created: " . $counters->getNodesCreated() . "\n";
    echo "Nodes deleted: " . $counters->getNodesDeleted() . "\n";
} catch (Exception $e) {
    // Handle errors
    echo 'Error: ' . $e->getMessage() . "\n";
    if ($e instanceof \Neo4j\QueryAPI\Exception\Neo4jException) {
        echo 'Neo4j Error Details: ' . json_encode($e->getErrorDetails(), JSON_PRETTY_PRINT) . "\n";
    }
}
