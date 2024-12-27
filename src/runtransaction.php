<?php

require __DIR__ . '/../vendor/autoload.php';

use Neo4j\QueryAPI\Transaction;

$baseUrl = 'https://6f72daa1.databases.neo4j.io';
$username = 'neo4j';
$password = '9lWmptqBgxBOz8NVcTJjgs3cHPyYmsy63ui6Spmw1d0';

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
