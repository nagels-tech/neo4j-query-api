<?php

// Include the necessary namespaces

use Neo4j\QueryAPI\Objects\ProfileQueryResults;

// Initialize the QueryAPI class with Neo4j URL and authentication header
$neo4jUrl = "http://localhost:7474";
$authHeader = "Basic bmVvNGo6dmVyeXNlY3JldA=="; // Replace with your actual auth credentials
$queryAPI = new Neo4j\QueryAPI\Objects\Api($neo4jUrl, $authHeader);  // Correct class name to QueryAPIQueryAPI

// Prepare the query and parameters
$query = "MATCH (n:Person {name: \$name}) RETURN n.name";  // Use escaped $ for the parameter
$parameters = ["name" => "Alice"];  // Use "Alice" as the parameter

    $result = $queryAPI->executeProfileQuery($query, $parameters);

    // Output the profiling results
    echo "DB Hits: " . $result->getDbHits() . "\n";
    echo "Page Cache Hits: " . $result->getPageCacheHits() . "\n";
    echo "Page Cache Misses: " . $result->getPageCacheMisses() . "\n";
    echo "Page Cache Hit Ratio: " . $result->getPageCacheHitRatio() . "\n";
    echo "Time: " . $result->getTime() . " ms\n";
    echo "Operator Type: " . $result->getOperatorType() . "\n";
    echo "Arguments: " . json_encode($result->getArguments()) . "\n";
   // echo "String Representation: " . $result->getStringRepresentation() . "\n";
    echo "Identifiers: " . json_encode($result->getIdentifiers()) . "\n";
    echo "Children: " . json_encode($result->getChildren()) . "\n";

