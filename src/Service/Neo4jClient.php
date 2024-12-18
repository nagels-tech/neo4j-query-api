<?php

namespace Neo4j\QueryAPI\Service;

use Neo4j\QueryAPI\Exception\Neo4jException;
use Exception;

class Neo4jClient
{
    public function executeQuery(string $query, array $parameters): void
    {
        try {
            // Simulated query execution
            // Replace this with actual query execution using your Neo4j client.
            throw new Exception(json_encode([
                'code' => 'Neo.DatabaseError.Database.UnableToStartDatabase',
                'message' => 'Unable to start database.'
            ]));
        } catch (Exception $e) {
            $errorResponse = json_decode($e->getMessage(), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                throw Neo4jException::fromNeo4jResponse($errorResponse);
            }

            // Fallback for unexpected exceptions
            throw new Neo4jException('Neo.UnknownError', $e->getMessage());
        }
    }
}
