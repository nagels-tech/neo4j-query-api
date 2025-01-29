<?php

require __DIR__ . '/../vendor/autoload.php'; // Autoload dependencies (assuming you're using Composer)

use Neo4j\QueryAPI\Neo4jRequestFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

// Neo4j configuration
$baseUri = "https://6f72daa1.databases.neo4j.io";
$username = "neo4j";
$password = "9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0";
$authHeader = "Basic " . base64_encode("{$username}:{$password}");

// Initialize the Neo4jRequestFactory
$httpFactory = new HttpFactory();
$requestFactory = new Neo4jRequestFactory($httpFactory, $httpFactory, $baseUri, $authHeader);

// Initialize Guzzle HTTP client
$client = new Client();

// Database and Cypher query configuration
$database = 'neo4j';
$cypher = 'MATCH (n) RETURN n LIMIT 10';
$parameters = []; // Optional query parameters

try {
    // Step 1: Start a new transaction
    $beginTxRequest = $requestFactory->buildBeginTransactionRequest($database);
    $beginTxResponse = $client->sendRequest($beginTxRequest);
    $beginTxData = json_decode($beginTxResponse->getBody()->getContents(), true);

    // Extract the transaction ID
    $transactionId = $beginTxData['transaction']['id'] ?? null;
    if (!$transactionId) {
        throw new RuntimeException("Transaction ID not found in response.");
    }

    echo "Transaction ID: {$transactionId}" . PHP_EOL;

    $runQueryRequest = $requestFactory->buildRunQueryRequest($database, $cypher, $parameters);
    $runQueryResponse = $client->sendRequest($runQueryRequest);

    $queryResults = json_decode($runQueryResponse->getBody()->getContents(), true);
    echo "Query Results: " . json_encode($queryResults, JSON_PRETTY_PRINT) . PHP_EOL;

    // Step 3: Commit the transaction
    $commitRequest = $requestFactory->buildCommitRequest($database, $transactionId);
    $commitResponse = $client->sendRequest($commitRequest);

    echo "Transaction committed successfully!" . PHP_EOL;

    // Optional: Output commit response
    echo "Commit Response: " . $commitResponse->getBody()->getContents() . PHP_EOL;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;

    // Rollback the transaction in case of failure
    if (isset($transactionId)) {
        $rollbackRequest = $requestFactory->buildRollbackRequest($database, $transactionId);
        $rollbackResponse = $client->sendRequest($rollbackRequest);

        echo "Transaction rolled back." . PHP_EOL;
        echo "Rollback Response: " . $rollbackResponse->getBody()->getContents() . PHP_EOL;
    }
}
