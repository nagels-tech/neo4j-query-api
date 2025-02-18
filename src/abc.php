<?php

require __DIR__ . '/../vendor/autoload.php';

use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Configuration;

$neo4jUsername = 'neo4j';
$neo4jPassword = 'OXDRMgdWFKMcBRCBrIwXnKkwLgDlmFxipnywT6t_AK0';
$neo4jUrl = 'https://bb79fe35.databases.neo4j.io';

$auth = Authentication::basic($neo4jUsername, $neo4jPassword);

$config = new Configuration(baseUri: $neo4jUrl);

$neo4j = Neo4jQueryAPI::login(
    $neo4jUrl,
    $auth,
    $config
);

$cypher = 'MATCH (n:Movie) RETURN n LIMIT 25';
$resultSet = $neo4j->run($cypher);

foreach ($resultSet as $row) {
    $node = $row['n'];

    $properties = $node->getProperties();

    if (isset($properties['title'])) {
        echo "Movie Title: " . $properties['title'] . "\n";
    } else {
        echo "Title not found.\n";
    }

    if (isset($properties['released'])) {
        echo "Movie Year: " . $properties['released'] . "\n";
    } else {
        echo "Year not found.\n";
    }
}
