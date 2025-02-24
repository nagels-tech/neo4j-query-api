<?php

namespace Neo4j\QueryAPI\Exception;

use Exception;

final class Neo4jException extends Exception
{
    public readonly string $errorCode;
    public readonly ?string $errorType;
    public readonly ?string $errorSubType;
    public readonly ?string $errorName;

    public function __construct(
        array       $errorDetails = [],
        int         $statusCode = 0,
        ?\Throwable $previous = null,
    ) {
        $this->errorCode = $errorDetails['code'] ?? 'Neo.UnknownError';
        $errorParts = explode('.', $this->errorCode);
        $this->errorType = $errorParts[1] ?? null;
        $this->errorSubType = $errorParts[2] ?? null;
        $this->errorName = $errorParts[3] ?? null;

        $message = $errorDetails['message'] ?? 'An unknown error occurred.';
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Create a Neo4jException instance from a Neo4j error response array.
     * @param array $response The error response from Neo4j.
     * @param \Throwable|null $exception Optional previous exception for chaining.
     * @return self
     */
    public static function fromNeo4jResponse(array $response, ?\Throwable $exception = null): self
    {
        $errorDetails = $response['errors'][0] ?? ['message' => 'Unknown error', 'code' => 'Neo.UnknownError'];


        return new self($errorDetails, previous: $exception);
    }

}
