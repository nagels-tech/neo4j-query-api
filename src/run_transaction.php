<?php

require __DIR__ . '/../vendor/autoload.php';
use Neo4j\QueryAPI\Transaction;

try {
    // Initialize the Transaction object with your Neo4j database credentials
    $neo4j = new Transaction('https://f2455ee6.databases.neo4j.io', 'neo4j', 'h5YLhuoSnPD6yMy8OwmFPXs6WkL8uX25zxHCKhiF_hY');

    // Begin a transaction
    $neo4j->beginTransaction();

    // Run Cypher queries within the transaction
    $neo4j->runQuery('CREATE (a:Person {name: $name})', ['name' => 'Alice']);
    $neo4j->runQuery('CREATE (b:Person {name: $name})', ['name' => 'Bob']);
    $neo4j->runQuery(
        'MATCH (a:Person {name: $name1}), (b:Person {name: $name2}) CREATE (a)-[:FRIEND]->(b)',
        ['name1' => 'Alice', 'name2' => 'Bob']
    );

    // Commit the transaction
    $neo4j->commitTransaction();
    echo "Transaction committed successfully.\n";

} catch (Exception $e) {
    // Handle error and rollback transaction if necessary
    if (isset($neo4j)) {
        try {
            $neo4j->rollbackTransaction();
            echo "Transaction rolled back successfully.\n";
        } catch (Exception $rollbackException) {
            echo "Failed to rollback transaction: " . $rollbackException->getMessage();
        }
    }
    echo "Transaction failed: " . $e->getMessage() . "\n";
}
