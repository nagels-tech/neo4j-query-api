<?php
/*
require 'vendor/autoload.php';

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Authentication\Authenticate;

$address = 'neo4j+s://bb79fe35.databases.neo4j.io';
$username = 'neo4j';
$password = 'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0';


    // Create a Neo4j client using the Laudis PHP driver with authentication
    $client = ClientBuilder::create()
        ->withDriver(
            'bolt',
            $address,
            Authenticate::basic($username, $password) // Proper authentication object
        )
        ->build();

    // Define the Cypher query
    $cypherQuery = 'MATCH (n:Person) RETURN n LIMIT 10';

    // Run the query and fetch results
    $results = $client->run($cypherQuery);

    // Print the results
    echo "<pre>";  // Optional: formats the output nicely for readability
    print_r($results->toArray());
    echo "</pre>";
*/
