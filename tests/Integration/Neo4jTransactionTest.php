<?php

namespace Neo4j\QueryAPI\Tests\Integration;
use PHPUnit\Framework\TestCase;
use Neo4j\QueryAPI\Transaction;

class Neo4jTransactionTest extends TestCase
{
    private Transaction $transaction;

    private $neo4jUrl = '***REMOVED***/';
    private $username = 'neo4j';
    private $password = '***REMOVED***';

    /**
     * Setup the test environment and initialize the Transaction object.
     */
    public function setUp(): void
    {
        $this->transaction = new Transaction($this->neo4jUrl, $this->username, $this->password);
    }

    /**
     * Test case for committing a transaction.
     */
    public function testTransactionCommit(): void
    {
        $transactionData = $this->transaction->startTransaction();
        $transactionId = $transactionData['transactionId'];
        $clusterAffinity = $transactionData['clusterAffinity'];

        $name = 'TestHuman_' . mt_rand(1, 100000);
        $query = "CREATE (x:Human {name: '$name'})";
        $this->transaction->run($query);

        $query = "MATCH (x:Human {name: '$name'}) RETURN x";
        $results = $this->transaction->run($query);
        $this->assertCount(2, $results);

        $query = "MATCH (x:Human {name: '$name'}) RETURN x";
        $results = $this->transaction->run($query);
        $this->assertCount(2, $results);

        $this->transaction->commit($transactionId, $clusterAffinity);

        $results = $this->transaction->run("MATCH (x:Human {name: '$name'}) RETURN x");
        $this->assertCount(2, $results);
    }

    /**
     * Test case for rolling back a transaction.
     */
    public function testTransactionRollback(): void
    {

        $transactionData = $this->transaction->startTransaction();
        $transactionId = $transactionData['transactionId'];
        $clusterAffinity = $transactionData['clusterAffinity'];

        $name = 'TestHuman_' . mt_rand(1, 100000);
        $query = "CREATE (x:Human {name: '$name'})";
        $this->transaction->run($query);


        $query = "MATCH (x:Human {name: '$name'}) RETURN x";
        $results = $this->transaction->run($query);
        $this->assertCount(2, $results);

        $query = "MATCH (x:Human {name: '$name'}) RETURN x";
        $results = $this->transaction->run($query);
        $this->assertCount(2, $results);

        $rollbackResponse = $this->transaction->rollback($transactionId, $clusterAffinity);
        $this->assertArrayHasKey('status', $rollbackResponse);

        $results = $this->transaction->run("MATCH (x:Human {name: '$name'}) RETURN x");
        $this->assertCount(2, $results);
    }
}
