<?php

require __DIR__.'/../vendor/autoload.php'; // Assuming you have Composer installed and dependencies loaded

use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Objects\Bookmarks;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use stdClass;

try {
    // Step 1: Initialize the connection to Neo4j with login credentials (authentication)
    $neo4jAddress = 'https://6f72daa1.databases.neo4j.io'; // Replace with your Neo4j database address
    $username = 'neo4j';
    $password = '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0';

    // Base64 encode the username and password for Basic Authentication
    $credentials = base64_encode("$username:$password");

    // Set up the authentication header with base64-encoded credentials
    $headers = [
        'Authorization' => 'Basic ' . $credentials,
        'Content-Type' => 'application/json',
    ];

    // Initialize the client with the authorization header
    $client = new \GuzzleHttp\Client([
        'base_uri' => rtrim($neo4jAddress, '/'),
        'timeout' => 10.0,
        'headers' => $headers,
    ]);

    // Step 2: Create the Cypher query
    $cypherQuery = 'MATCH (n) RETURN n LIMIT 10';
    $parameters = []; // No parameters in this query
    $database = 'neo4j'; // Default Neo4j database

    echo "Running Cypher Query: $cypherQuery\n";

    // Prepare the payload for the Cypher query
    $payload = [
        'statement' => $cypherQuery,
        'parameters' => new stdClass(), // No parameters
        'includeCounters' => true,
    ];

    // Step 3: Send the request to Neo4j
    $response = $client->post("/db/{$database}/query/v2", [
        'json' => $payload,
    ]);

    // Parse the response body as JSON
    $responseData = json_decode($response->getBody()->getContents(), true);

    // Check for errors in the response
    if (isset($responseData['errors']) && count($responseData['errors']) > 0) {
        echo "Error: " . $responseData['errors'][0]['message'] . "\n";
    } else {
        // Step 4: Output the result of the query
        echo "Query Results:\n";
        foreach ($responseData['data'] as $row) {
            print_r($row); // Print each row's data
        }
    }

    // Step 5: Begin a new transaction
    $transactionResponse = $client->post("/db/neo4j/query/v2/tx");
    $transactionData = json_decode($transactionResponse->getBody()->getContents(), true);
    $transactionId = $transactionData['transaction']['id'];  // Retrieve the transaction ID

    echo "Transaction started successfully.\n";
    echo "Transaction ID: $transactionId\n";

    // You can also fetch additional transaction details if available in the response
    // Example: transaction metadata or counters
    if (isset($transactionData['transaction']['metadata'])) {
        echo "Transaction Metadata: \n";
        print_r($transactionData['transaction']['metadata']);
    }

    // Step 6: Execute a query within the transaction
    $cypherTransactionQuery = 'MATCH (n) SET n.modified = true RETURN n LIMIT 5';
    $transactionPayload = [
        'statement' => $cypherTransactionQuery,
        'parameters' => new stdClass(),  // No parameters
    ];

    // Execute the transaction query
    $transactionQueryResponse = $client->post("/db/neo4j/query/v2/tx/{$transactionId}/commit", [
        'json' => $transactionPayload,
    ]);

    $transactionQueryData = json_decode($transactionQueryResponse->getBody()->getContents(), true);

    // Check for any errors in the transaction query
    if (isset($transactionQueryData['errors']) && count($transactionQueryData['errors']) > 0) {
        echo "Transaction Error: " . $transactionQueryData['errors'][0]['message'] . "\n";
    } else {
        echo "Transaction Query Results:\n";
        print_r($transactionQueryData['data']);  // Print transaction results
    }

} catch (RequestException $e) {
    echo "Request Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
