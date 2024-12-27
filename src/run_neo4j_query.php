<?php

$neo4j_url = 'https://f2455ee6.databases.neo4j.io';
$username = 'neo4j';
$password = 'h5YLhuoSnPD6yMy8OwmFPXs6WkL8uX25zxHCKhiF_hY';

function runNeo4jTransaction($query): void
{
    global $neo4j_url, $username, $password;

    $ch = curl_init();

    echo "Authorization Header: " . base64_encode("$username:$password") . "\n";

    curl_setopt($ch, CURLOPT_URL, $neo4j_url . '/db/neo4j/transaction/commit');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode("$username:$password")
    ]);

    $payload = json_encode([
        "statements" => []
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    if(curl_errno($ch)) {
        echo 'Error starting transaction: ' . curl_error($ch);
        return;
    }

    echo "Transaction Start Response: ";
    print_r($response);

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_status === 403) {
        echo "403 Forbidden: Check credentials and permissions.\n";
    }

    $transaction_data = json_decode($response, true);

    if (!isset($transaction_data['results'])) {
        echo "Transaction creation failed or missing results. Response: ";
        print_r($transaction_data);
        return;
    }

    $query_data = [
        "statements" => [
            [
                "statement" => $query
            ]
        ]
    ];

    curl_setopt($ch, CURLOPT_URL, $neo4j_url . '/db/neo4j/query/v2/tx');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query_data));

    $response = curl_exec($ch);
    if(curl_errno($ch)) {
        echo 'Error running query: ' . curl_error($ch);
        return;
    }

    $commit_data = json_decode($response, true);
    if (isset($commit_data['errors']) && count($commit_data['errors']) > 0) {
        echo "Query error: " . $commit_data['errors'][0]['message'];
        return;
    }

    echo "Transaction successful. Data returned: ";
    print_r($commit_data);

    curl_close($ch);
}

$query = 'MATCH (n) RETURN n LIMIT 5';

runNeo4jTransaction($query);