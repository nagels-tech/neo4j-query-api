<?php

use Neo4j\QueryAPI\Neo4jQueryAPI;
use GuzzleHttp\Exception\RequestException;

require __DIR__ . '/../vendor/autoload.php';

try {
    // Login to the Neo4j instance
    $api = Neo4jQueryAPI::login(
        'https://bb79fe35.databases.neo4j.io', // Replace with your Neo4j instance URL
        'neo4j',                               // Replace with your Neo4j username
        'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0' // Replace with your Neo4j password

    );

    // Define a Cypher query
    $query = "MATCH (n:Person {DateTime:'2024-12-11T11:00:00Z'}) RETURN n LIMIT 10";

    // Fetch results in plain JSON format
    $plainResults = $api->run($query, [], 'neo4j', false);
    echo "Plain JSON Results:\n";
    echo "<pre>";
    print_r($plainResults);
    echo "</pre>";

    // Fetch results in Neo4j-extended JSON format
    $extendedResults = $api->run($query, [], 'neo4j', true);
    echo "Extended JSON Results:\n";
    echo "<pre>";
    print_r($extendedResults);
    echo "</pre>";

} catch (RequestException $e) {
    echo "Request Error: " . $e->getMessage();
} catch (RuntimeException $e) {
    echo "Runtime Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage();
}

