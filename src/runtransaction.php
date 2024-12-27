<?php

require __DIR__ . '/../vendor/autoload.php';

use Neo4j\QueryAPI\Transaction;

$baseUrl = '***REMOVED***';
$username = 'neo4j';
$password = '***REMOVED***';

try {
    $transaction = new Transaction($baseUrl, $username, $password);

    $query = 'CREATE (n:Person {name: $name, age: $age}) RETURN n';
    $parameters = [
        'name' => 'Alice',
        'age' => 30,
    ];

    $resultSet = $transaction->run($query, $parameters);

    echo "Query executed successfully. Results:\n";
    foreach ($resultSet as $row) {
        print_r($row);
    }

    $transaction->commit();
    echo "Transaction committed successfully.\n";

} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
