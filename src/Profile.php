<?php

namespace Neo4j\QueryAPI;
require __DIR__ . '/../vendor/autoload.php';


use GuzzleHttp\Client;

class Profile
{
    private $neo4jUrl;
    private $username;
    private $password;
    private $client;

    public function __construct($url, $username, $password)
    {
        $this->neo4jUrl = $url;
        $this->username = $username;
        $this->password = $password;
        $this->client = new Client();
    }

    public function executeQuery($query ,$parameters=[])
    {
        $response = $this->client->post($this->neo4jUrl, [
            'auth' => [$this->username, $this->password],
            'json' => [
                'statement' => $query,
                'parameters'=>$parameters
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function formatResponse($data): array
    {
        $output = [
            "data" => [
                "fields" => [],
                "values" => []
            ],
            "profiledQueryPlan" => [],
            "bookmarks" => $data['bookmarks'] ?? []
        ];

        if (isset($data['result']['columns']) && isset($data['result']['rows'])) {
            $output["data"]["fields"] = $data['result']['columns'];
            foreach ($data['result']['rows'] as $row) {
                $output["data"]["values"][] = $row;
            }
        }

        if (isset($data['profiledQueryPlan'])) {
            $output["profiledQueryPlan"] = $data['profiledQueryPlan'];
        }

        return $output;
    }
}

