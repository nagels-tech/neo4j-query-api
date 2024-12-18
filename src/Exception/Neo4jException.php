<?php

namespace Neo4j\QueryAPI\Exception;

use Exception;

class Neo4jException extends Exception
{
    private string $errorCode;

    public function __construct(string $errorCode, string $message, int $code = 0, Exception $previous = null)
    {
        $this->errorCode = $errorCode;
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public static function fromNeo4jResponse(array $response): self
    {
        $errorCode = $response['code'] ?? 'Neo.UnknownError';
        $message = $response['message'] ?? 'An unknown error occurred.';
        return new self($errorCode, $message);
    }
}
