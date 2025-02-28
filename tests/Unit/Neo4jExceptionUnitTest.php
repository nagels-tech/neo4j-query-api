<?php

namespace Neo4j\QueryAPI\Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Exception\Neo4jException;

/**
 *  @api
 */
final class Neo4jExceptionUnitTest extends TestCase
{
    /**
     * Test the constructor and property initialization.
     */
    public function testConstructor(): void
    {
        $errorDetails = [
            'code' => 'Neo.ClientError.Statement.SyntaxError',
            'message' => 'Invalid syntax near ...',
            'statusCode' => 400
        ];

        $exception = new Neo4jException($errorDetails);

        $this->assertSame('Neo.ClientError.Statement.SyntaxError', $exception->getErrorCode());
        $this->assertSame('ClientError', $exception->getType());
        $this->assertSame('Statement', $exception->getSubType());
        $this->assertSame('SyntaxError', $exception->getName());
        $this->assertSame('Invalid syntax near ...', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    /**
     * Test the handling of missing error details.
     */
    public function testConstructorWithMissingErrorDetails(): void
    {
        $exception = new Neo4jException([]);

        $this->assertSame('Neo.UnknownError', $exception->getErrorCode());
        $this->assertSame('UnknownError', $exception->getType());
        $this->assertNull($exception->getSubType());
        $this->assertNull($exception->getName());
        $this->assertSame('An unknown error occurred.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    /**
     * Test the `fromNeo4jResponse` static method with valid input.
     */
    public function testFromNeo4jResponse(): void
    {
        $response = [
            'errors' => [
                [
                    'code' => 'Neo.ClientError.Transaction.InvalidRequest',
                    'message' => 'Transaction error occurred.',
                    'statusCode' => 500
                ]
            ]
        ];

        $exception = Neo4jException::fromNeo4jResponse($response);

        $this->assertSame('Neo.ClientError.Transaction.InvalidRequest', $exception->getErrorCode());
        $this->assertSame('ClientError', $exception->getType());
        $this->assertSame('Transaction', $exception->getSubType());
        $this->assertSame('InvalidRequest', $exception->getName());
        $this->assertSame('Transaction error occurred.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    /**
     * Test the `fromNeo4jResponse` static method with missing error details.
     */
    public function testFromNeo4jResponseWithMissingDetails(): void
    {
        $response = ['errors' => []];

        $exception = Neo4jException::fromNeo4jResponse($response);

        $this->assertSame('Neo.UnknownError', $exception->getErrorCode());
        $this->assertSame('UnknownError', $exception->getType());
        $this->assertNull($exception->getSubType());
        $this->assertNull($exception->getName());
        $this->assertSame(0, $exception->getCode());
    }

    /**
     * Test the `fromNeo4jResponse` static method with null response.
     */
    public function testFromNeo4jResponseWithNullResponse(): void
    {
        $response = ['errors' => null];

        $exception = Neo4jException::fromNeo4jResponse($response);

        $this->assertSame('Neo.UnknownError', $exception->getErrorCode());
        $this->assertSame('UnknownError', $exception->getType());
        $this->assertNull($exception->getSubType(), "Expected 'getSubType()' to return null for null response");
        $this->assertNull($exception->getName(), "Expected 'getName()' to return null for null response");
        $this->assertSame(0, $exception->getCode());
    }

    /**
     * Test exception chaining.
     */
    public function testExceptionChaining(): void
    {
        $previousException = new Exception('Previous exception');

        $errorDetails = [
            'code' => 'Neo.ClientError.Security.Unauthorized',
            'message' => 'Authentication failed.',
            'statusCode' => 401
        ];

        $exception = new Neo4jException($errorDetails, $errorDetails['statusCode'], $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
        $this->assertSame('Unauthorized', $exception->getName());
    }
}
