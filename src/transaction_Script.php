<?php

require __DIR__ . '/../vendor/autoload.php'; // Assuming you have Composer installed

use Psr\Http\Client\ClientInterface;
use GuzzleHttp\Client;
use Neo4j\QueryAPI\Transaction;

// Neo4j connection parameters
$neo4jAddress = 'https://6f72daa1.databases.neo4j.io';
$username = 'neo4j';
$password = '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0';

// Initialize the HTTP client (use Guzzle or any PSR-18 compatible HTTP client)
$client = new Client();

// Function to start a transaction and extract the transactionId and clusterAffinity
function startTransaction($client, $neo4jAddress, $username, $password)
{
    // Base64 encode the credentials for basic authentication
    $credentials = base64_encode("$username:$password");

    // Make an initial request to Neo4j to begin a transaction (NOT using /commit)
    $response = $client->post("{$neo4jAddress}/db/neo4j/tx", [
        'headers' => [
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/json',
        ]
    ]);

    // Extract the transaction ID and cluster affinity from the response
    $data = json_decode($response->getBody()->getContents(), true);

    // Check if the transaction was created and extract necessary values
    if (isset($data['tx'])) {
        $transactionId = $data['tx']['id'];
        $clusterAffinity = $data['neo4j-cluster-affinity'];  // Usually returned as part of response headers

        return [$transactionId, $clusterAffinity];
    }

    throw new Exception("Failed to start transaction or missing transaction ID in the response.");
}

// Start the transaction and extract transactionId and clusterAffinity
list($transactionId, $clusterAffinity) = startTransaction($client, $neo4jAddress, $username, $password);

// Create a new Transaction instance with the extracted values
$transaction = new Transaction($client, $clusterAffinity, $transactionId);

// Function to run a Cypher query
function runQuery($transaction, $query, $parameters = [])
{
    try {
        $results = $transaction->run($query, $parameters);
        echo "Query Results:\n";
        foreach ($results->getRows() as $row) {
            print_r($row->getData());
        }
    } catch (Exception $e) {
        echo "Error running query: " . $e->getMessage() . "\n";
    }
}

// Function to commit the transaction
function commitTransaction($transaction)
{
    try {
        $transaction->commit();
        echo "Transaction committed successfully.\n";
    } catch (Exception $e) {
        echo "Error committing transaction: " . $e->getMessage() . "\n";
    }
}

// Function to rollback the transaction
function rollbackTransaction($transaction)
{
    try {
        $transaction->rollback();
        echo "Transaction rolled back successfully.\n";
    } catch (Exception $e) {
        echo "Error rolling back transaction: " . $e->getMessage() . "\n";
    }
}

// Example usage: running a query within the transaction
$query = "CREATE (n:Person {name: 'John Doe'}) RETURN n";
runQuery($transaction, $query);

// Now, let's commit the transaction
commitTransaction($transaction);

// Running another query after commit to verify changes
$query = "MATCH (n:Person {name: 'John Doe'}) RETURN n";
runQuery($transaction, $query);
