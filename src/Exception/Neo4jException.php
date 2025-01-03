<?php

namespace Neo4j\QueryAPI\Exception;

use Exception;

class Neo4jException extends Exception
{
    private readonly string $errorCode;
    private readonly ?string $errorType;
    private readonly ?string $errorSubType;
    private readonly ?string $errorName;

    public function __construct(
        array       $errorDetails = [],
        int         $statusCode = 0,
        ?\Throwable $previous = null
    )
    {
        $this->errorCode = $errorDetails['code'] ?? 'Neo.UnknownError';
        $errorParts = explode('.', $this->errorCode);
        $this->errorType = $errorParts[1] ?? null;
        $this->errorSubType = $errorParts[2] ?? null;
        $this->errorName = $errorParts[3] ?? null;


        $message = $errorDetails['message'] ?? 'An unknown error occurred.';
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get the Neo4j error code associated with this exception.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getType(): ?string
    {
        return $this->errorType;
    }

    public function getSubType(): ?string
    {
        return $this->errorSubType;
    }

    public function getName(): ?string
    {
        return $this->errorName;
    }

    /**
     * Create a Neo4jException instance from a Neo4j error response array.
     *
     * @param array $response The error response from Neo4j.
     * @param \Throwable|null $exception Optional previous exception for chaining.
     * @return self
     */
    public static function fromNeo4jResponse(array $response, ?\Throwable $exception = null): self
    {
        $errorDetails = $response['errors'][0] ?? [];
        $statusCode = $errorDetails['statusCode'] ?? 0;

        return new self($errorDetails, (int)$statusCode, $exception);
    }
}
