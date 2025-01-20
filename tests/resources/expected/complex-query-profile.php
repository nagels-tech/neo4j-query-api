<?php

use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlanArguments;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Results\ResultSet;

return new ResultSet(
    rows: [],
    counters: new ResultCounters(
        containsUpdates: false,
        nodesCreated: 0,
        nodesDeleted: 0,
        propertiesSet: 0,
        relationshipsCreated: 0,
        relationshipsDeleted: 0,
        labelsAdded: 0,
        labelsRemoved: 0,
        indexesAdded: 0,
        indexesRemoved: 0,
        constraintsAdded: 0,
        constraintsRemoved: 0,
        containsSystemUpdates: false,
        systemUpdates: 0
    ),
    bookmarks: new Bookmarks([]),
    profiledQueryPlan: new ProfiledQueryPlan(
        dbHits: 0,
        records: 0,
        hasPageCacheStats: false,
        pageCacheHits: 0,
        pageCacheMisses: 0,
        pageCacheHitRatio: 0.0,
        time: 0,
        operatorType: "ProduceResults@neo4j",
        arguments: new ProfiledQueryPlanArguments(
            globalMemory: 10624,
            plannerImpl: "IDP",
            memory: null,
            stringRepresentation: "Cypher 5\n\nPlanner COST\n\nRuntime PIPELINED\n\nRuntime version 5.26\n\nBatch size 128\n\n+----------------------+----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| Operator             | Id | Details                                          | Estimated Rows | Rows  | DB Hits | Memory (Bytes) | Page Cache Hits/Misses | Time (ms) | Pipeline            |\n+----------------------+----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| +ProduceResults      |  0 |                                                  |              2 |     0 |       0 |                |                    0/0 |     0.000 |                     |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+                     |\n| +EmptyResult         |  1 |                                                  |              2 |     0 |       0 |                |                    0/0 |     0.000 |                     |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+                     |\n| +Create              |  2 | (a)-[anon_0:KNOWS]->(b), (b)-[anon_1:KNOWS]->(a) |              2 |     0 |       0 |                |                    0/0 |     0.000 |                     |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+                     |\n| +Filter              |  3 | cache[a.id] < cache[b.id]                        |              2 |     0 |       0 |                |                    0/0 |     0.000 | In Pipeline 3       |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| +Apply               |  4 |                                                  |              8 |     0 |       0 |                |                    0/0 |           |                     |\n| |\\                   +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| | +CartesianProduct  |  5 |                                                  |              8 |     0 |       0 |           1392 |                        |           | In Pipeline 3       |\n| | |\\                 +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| | | +Filter          |  6 | cache[b.id] = j                                  |             50 |     0 |       0 |                |                        |           |                     |\n| | | |                +----+--------------------------------------------------+----------------+-------+---------+----------------+                        |           |                     |\n| | | +NodeByLabelScan |  7 | b:Person                                         |           1000 |     0 |       0 |            256 |                    0/0 |     0.000 | Fused in Pipeline 2 |\n| | |                  +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| | +Filter            |  8 | rand() < \$autodouble_4 AND cache[a.id] = i       |             15 |     0 |    2027 |                |                        |           |                     |\n| | |                  +----+--------------------------------------------------+----------------+-------+---------+----------------+                        |           |                     |\n| | +NodeByLabelScan   |  9 | a:Person                                         |           1000 | 20000 |   30000 |           8488 |                10002/0 |     6.158 | Fused in Pipeline 1 |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| +Unwind              | 10 | range(\$autoint_2, \$autoint_3) AS j               |            100 | 10000 |       0 |                |                        |           |                     |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+                        |           |                     |\n| +Unwind              | 11 | range(\$autoint_0, \$autoint_1) AS i               |             10 |   100 |       0 |                |                    0/0 |     0.000 | Fused in Pipeline 0 |\n+----------------------+----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n\nTotal database accesses: 32027, total allocated memory: 10624\n",
            runtime: "PIPELINED",
            time: 0,
            pageCacheMisses: 0,
            pageCacheHits: 0,
            runtimeImpl: "PIPELINED",
            version: 5,
            dbHits: 0,
            batchSize: 128,
            details: "",
            plannerVersion: 5.26,
            pipelineInfo: "In Pipeline 3",
            runtimeVersion: 5.26,
            id: 0,
            estimatedRows: 2.25,
            planner: 'COST',
            rows: 0,
        ),
        children: [
            // CHILD #1: EmptyResult@neo4j
            new ProfiledQueryPlan(
                dbHits: 0,
                records: 0,
                hasPageCacheStats: false,
                pageCacheHits: 0,
                pageCacheMisses: 0,
                pageCacheHitRatio: 0.0,
                time: 0,
                operatorType: "EmptyResult@neo4j",
                arguments: new ProfiledQueryPlanArguments(
                    plannerImpl: null,
                    memory: null,
                    stringRepresentation: null,
                    runtime: null,
                    time: 0,
                    pageCacheMisses: 0,
                    pageCacheHits: 0,
                    runtimeImpl: null,
                    version: null,
                    dbHits: 0,
                    batchSize: null,
                    details: null,
                    // Using empty strings or nulls for fields not strictly needed
                    plannerVersion: null,
                    pipelineInfo: 'In Pipeline 3',
                    runtimeVersion: null,
                    id: 1,
                    estimatedRows: 2.25,
                    rows: 0
                ),
                children: [
                    // CHILD #1.1: Create@neo4j
                    new ProfiledQueryPlan(
                        dbHits: 0,
                        records: 0,
                        hasPageCacheStats: false,
                        pageCacheHits: 0,
                        pageCacheMisses: 0,
                        pageCacheHitRatio: 0.0,
                        time: 0,
                        operatorType: "Create@neo4j",
                        arguments: new ProfiledQueryPlanArguments(
                            plannerImpl: null,
                            memory: null,
                            stringRepresentation: null,
                            runtime: null,
                            time: 0,
                            pageCacheMisses: null,
                            pageCacheHits: 0,
                            runtimeImpl: null,
                            version: null,
                            dbHits: 0,
                            batchSize: 0,
                            details: "(a)-[anon_0:KNOWS]->(b), (b)-[anon_1:KNOWS]->(a)",
                            plannerVersion: "",
                            pipelineInfo: "In Pipeline 3",
                            runtimeVersion: null,
                            id: 2,
                            estimatedRows: 2.25,
                            rows: 0
                        ),
                        children: [
                            // CHILD #1.1.1: Filter@neo4j (id=3)
                            new ProfiledQueryPlan(
                                dbHits: 0,
                                records: 0,
                                hasPageCacheStats: false,
                                pageCacheHits: 0,
                                pageCacheMisses: 0,
                                pageCacheHitRatio: 0.0,
                                time: 0,
                                operatorType: "Filter@neo4j",
                                arguments: new ProfiledQueryPlanArguments(
                                    plannerImpl: null,
                                    memory: null,
                                    stringRepresentation: null,
                                    runtime: null,
                                    time: 0,
                                    pageCacheMisses: null,
                                    pageCacheHits: 0,
                                    runtimeImpl: null,
                                    version: null,
                                    dbHits: 0,
                                    batchSize: 0,
                                    details: "cache[a.id] < cache[b.id]",
                                    plannerVersion: "",
                                    pipelineInfo: "In Pipeline 3",
                                    runtimeVersion: null,
                                    id: 3,
                                    estimatedRows: 2.25,
                                    rows: 0
                                ),
                                children: [
                                    // CHILD #1.1.1.1: Apply@neo4j (id=4)
                                    new ProfiledQueryPlan(
                                        dbHits: 0,
                                        records: 0,
                                        hasPageCacheStats: false,
                                        pageCacheHits: 0,
                                        pageCacheMisses: 0,
                                        pageCacheHitRatio: 0.0,
                                        time: 0,
                                        operatorType: "Apply@neo4j",
                                        arguments: new ProfiledQueryPlanArguments(
                                            plannerImpl: null,
                                            memory: null,
                                            stringRepresentation: null,
                                            runtime: null,
                                            time: null,
                                            pageCacheMisses: null,
                                            pageCacheHits: 0,
                                            runtimeImpl: null,
                                            version: null,
                                            dbHits: 0,
                                            batchSize: 0,
                                            details: "",
                                            plannerVersion: "",
                                            pipelineInfo: "",
                                            runtimeVersion: null,
                                            id: 4,
                                            estimatedRows: 7.5,
                                            rows: 0
                                        ),
                                        children: [
                                            // CHILD #1.1.1.1.1: Unwind@neo4j (id=10)
                                            new ProfiledQueryPlan(
                                                dbHits: 0,
                                                records: 10000,
                                                hasPageCacheStats: false,
                                                pageCacheHits: 0,
                                                pageCacheMisses: 0,
                                                pageCacheHitRatio: 0.0,
                                                time: 0,
                                                operatorType: "Unwind@neo4j",
                                                arguments: new ProfiledQueryPlanArguments(
                                                    plannerImpl: null,
                                                    memory: null,
                                                    stringRepresentation: null,
                                                    runtime: null,
                                                    time: null,
                                                    pageCacheMisses: null,
                                                    pageCacheHits: 0,
                                                    runtimeImpl: null,
                                                    version: null,
                                                    dbHits: 0,
                                                    batchSize: 0,
                                                    details: "range(\$autoint_2, \$autoint_3) AS j",
                                                    plannerVersion: "",
                                                    pipelineInfo: "Fused in Pipeline 0",
                                                    runtimeVersion: null,
                                                    id: 10,
                                                    estimatedRows: 100.0,
                                                    rows: 10000
                                                ),
                                                children: [
                                                    // The second Unwind@neo4j (id=11)
                                                    new ProfiledQueryPlan(
                                                        dbHits: 0,
                                                        records: 100,
                                                        hasPageCacheStats: false,
                                                        pageCacheHits: 0,
                                                        pageCacheMisses: 0,
                                                        pageCacheHitRatio: 0.0,
                                                        time: 0,
                                                        operatorType: "Unwind@neo4j",
                                                        arguments: new ProfiledQueryPlanArguments(
                                                            plannerImpl: null,
                                                            memory: null,
                                                            stringRepresentation: null,
                                                            runtime: null,
                                                            time: 0,
                                                            pageCacheMisses: null,
                                                            pageCacheHits: 0,
                                                            runtimeImpl: null,
                                                            version: null,
                                                            dbHits: 0,
                                                            batchSize: 0,
                                                            details: "range(\$autoint_0, \$autoint_1) AS i",
                                                            plannerVersion: "",
                                                            pipelineInfo: "Fused in Pipeline 0",
                                                            runtimeVersion: null,
                                                            id: 11,
                                                            estimatedRows: 10.0,
                                                            rows: 100
                                                        )
                                                    )
                                                ]
                                            ),
                                            // CHILD #1.1.1.1.2: CartesianProduct@neo4j (id=5)
                                            new ProfiledQueryPlan(
                                                dbHits: 0,
                                                records: 0,
                                                hasPageCacheStats: false,
                                                pageCacheHits: 0,
                                                pageCacheMisses: 0,
                                                pageCacheHitRatio: 0.0,
                                                time: 0,
                                                operatorType: "CartesianProduct@neo4j",
                                                arguments: new ProfiledQueryPlanArguments(
                                                    plannerImpl: null,
                                                    memory: 1392,
                                                    stringRepresentation: null,
                                                    runtime: null,
                                                    time: null,
                                                    pageCacheMisses: null,
                                                    pageCacheHits: 0,
                                                    runtimeImpl: null,
                                                    version: null,
                                                    dbHits: 0,
                                                    batchSize: 0,
                                                    details: "",
                                                    plannerVersion: "",
                                                    pipelineInfo: "In Pipeline 3",
                                                    runtimeVersion: null,
                                                    id: 5,
                                                    estimatedRows: 7.5,
                                                    rows: 0
                                                ),
                                                children: [
                                                    // CHILD #1.1.1.1.2.1: Filter@neo4j (id=8)
                                                    new ProfiledQueryPlan(
                                                        dbHits: 2027,
                                                        records: 0,
                                                        hasPageCacheStats: false,
                                                        pageCacheHits: 0,
                                                        pageCacheMisses: 0,
                                                        pageCacheHitRatio: 0.0,
                                                        time: 0,
                                                        operatorType: "Filter@neo4j",
                                                        arguments: new ProfiledQueryPlanArguments(
                                                            plannerImpl: null,
                                                            memory: null,
                                                            stringRepresentation: null,
                                                            runtime: null,
                                                            time: null,
                                                            pageCacheMisses: null,
                                                            pageCacheHits: 0,
                                                            runtimeImpl: null,
                                                            version: null,
                                                            dbHits: 2027,
                                                            batchSize: 0,
                                                            details: "rand() < \$autodouble_4 AND cache[a.id] = i",
                                                            plannerVersion: "",
                                                            pipelineInfo: "Fused in Pipeline 1",
                                                            runtimeVersion: null,
                                                            id: 8,
                                                            estimatedRows: 15.0,
                                                            rows: 0
                                                        ),
                                                        children: [
                                                            // NodeByLabelScan@neo4j (id=9)
                                                            new ProfiledQueryPlan(
                                                                dbHits: 30000,
                                                                records: 20000,
                                                                hasPageCacheStats: true,
                                                                pageCacheHits: 10002,
                                                                pageCacheMisses: 0,
                                                                pageCacheHitRatio: 1.0,
                                                                time: 6157670,
                                                                operatorType: "NodeByLabelScan@neo4j",
                                                                arguments: new ProfiledQueryPlanArguments(
                                                                    plannerImpl: null,
                                                                    memory: 8488,
                                                                    stringRepresentation: null,
                                                                    runtime: null,
                                                                    time: 6157670,
                                                                    pageCacheMisses: 0,
                                                                    pageCacheHits: 10002,
                                                                    runtimeImpl: null,
                                                                    version: null,
                                                                    dbHits: 30000,
                                                                    batchSize: 0,
                                                                    details: "a:Person",
                                                                    plannerVersion: "",
                                                                    pipelineInfo: "Fused in Pipeline 1",
                                                                    runtimeVersion: null,
                                                                    id: 9,
                                                                    estimatedRows: 1000.0,
                                                                    rows: 20000,
                                                                )
                                                            )
                                                        ]
                                                    ),
                                                    // CHILD #1.1.1.1.2.2: Filter@neo4j (id=6)
                                                    new ProfiledQueryPlan(
                                                        dbHits: 0,
                                                        records: 0,
                                                        hasPageCacheStats: false,
                                                        pageCacheHits: 0,
                                                        pageCacheMisses: 0,
                                                        pageCacheHitRatio: 0.0,
                                                        time: 0,
                                                        operatorType: "Filter@neo4j",
                                                        arguments: new ProfiledQueryPlanArguments(
                                                            plannerImpl: null,
                                                            memory: null,
                                                            stringRepresentation: null,
                                                            runtime: null,
                                                            time: null,
                                                            pageCacheMisses: null,
                                                            pageCacheHits: 0,
                                                            runtimeImpl: null,
                                                            version: null,
                                                            dbHits: 0,
                                                            batchSize: 0,
                                                            details: "cache[b.id] = j",
                                                            plannerVersion: "",
                                                            pipelineInfo: "Fused in Pipeline 2",
                                                            runtimeVersion: null,
                                                            id: 6,
                                                            estimatedRows: 50.0,
                                                            rows: 0
                                                        ),
                                                        children: [
                                                            // NodeByLabelScan@neo4j (id=7)
                                                            new ProfiledQueryPlan(
                                                                dbHits: 0,
                                                                records: 0,
                                                                hasPageCacheStats: false,
                                                                pageCacheHits: 0,
                                                                pageCacheMisses: 0,
                                                                pageCacheHitRatio: 0.0,
                                                                time: 0,
                                                                operatorType: "NodeByLabelScan@neo4j",
                                                                arguments: new ProfiledQueryPlanArguments(
                                                                    plannerImpl: null,
                                                                    memory: 256,
                                                                    stringRepresentation: null,
                                                                    runtime: null,
                                                                    time: 0,
                                                                    pageCacheMisses: null,
                                                                    pageCacheHits: 0,
                                                                    runtimeImpl: null,
                                                                    version: null,
                                                                    dbHits: 0,
                                                                    batchSize: 0,
                                                                    details: "b:Person",
                                                                    plannerVersion: "",
                                                                    pipelineInfo: "Fused in Pipeline 2",
                                                                    runtimeVersion: null,
                                                                    id: 7,
                                                                    estimatedRows: 1000.0,
                                                                    rows: 0
                                                                )
                                                            )
                                                        ]
                                                    )
                                                ]
                                            )
                                        ]
                                    )
                                ]
                            )
                        ]
                    )
                ]
            )
        ]
    )
);
