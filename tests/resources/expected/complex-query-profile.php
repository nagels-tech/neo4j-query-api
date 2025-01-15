<?php

use Neo4j\QueryAPI\Objects\Bookmarks;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlanArguments;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Results\ResultSet;

return new ResultSet(
    rows: [],
    counters: new ResultCounters(
        false,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        false,
        0
    ),
    bookmarks: new Bookmarks([]),
    profiledQueryPlan: new ProfiledQueryPlan(
        0,
        0,
        false,
        0,
        0,
        0.0,
        0,
        "ProduceResults@neo4j",

        new ProfiledQueryPlanArguments(
            10624,
            "IDP",
            null,
            "Cypher 5\n\nPlanner COST\n\nRuntime PIPELINED\n\nRuntime version 5.26\n\nBatch size 128\n\n+----------------------+----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| Operator             | Id | Details                                          | Estimated Rows | Rows  | DB Hits | Memory (Bytes) | Page Cache Hits/Misses | Time (ms) | Pipeline            |\n+----------------------+----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| +ProduceResults      |  0 |                                                  |              2 |     0 |       0 |                |                    0/0 |     0.000 |                     |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+                     |\n| +EmptyResult         |  1 |                                                  |              2 |     0 |       0 |                |                    0/0 |     0.000 |                     |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+                     |\n| +Create              |  2 | (a)-[anon_0:KNOWS]->(b), (b)-[anon_1:KNOWS]->(a) |              2 |     0 |       0 |                |                    0/0 |     0.000 |                     |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+                     |\n| +Filter              |  3 | cache[a.id] < cache[b.id]                        |              2 |     0 |       0 |                |                    0/0 |     0.000 | In Pipeline 3       |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| +Apply               |  4 |                                                  |              8 |     0 |       0 |                |                    0/0 |           |                     |\n| |\\                   +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| | +CartesianProduct  |  5 |                                                  |              8 |     0 |       0 |           1392 |                        |           | In Pipeline 3       |\n| | |\\                 +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| | | +Filter          |  6 | cache[b.id] = j                                  |             50 |     0 |       0 |                |                        |           |                     |\n| | | |                +----+--------------------------------------------------+----------------+-------+---------+----------------+                        |           |                     |\n| | | +NodeByLabelScan |  7 | b:Person                                         |           1000 |     0 |       0 |            256 |                    0/0 |     0.000 | Fused in Pipeline 2 |\n| | |                  +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| | +Filter            |  8 | rand() < \$autodouble_4 AND cache[a.id] = i       |             15 |     0 |    2027 |                |                        |           |                     |\n| | |                  +----+--------------------------------------------------+----------------+-------+---------+----------------+                        |           |                     |\n| | +NodeByLabelScan   |  9 | a:Person                                         |           1000 | 20000 |   30000 |           8488 |                10002/0 |     6.158 | Fused in Pipeline 1 |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n| +Unwind              | 10 | range(\$autoint_2, \$autoint_3) AS j               |            100 | 10000 |       0 |                |                        |           |                     |\n| |                    +----+--------------------------------------------------+----------------+-------+---------+----------------+                        |           |                     |\n| +Unwind              | 11 | range(\$autoint_0, \$autoint_1) AS i               |             10 |   100 |       0 |                |                    0/0 |     0.000 | Fused in Pipeline 0 |\n+----------------------+----+--------------------------------------------------+----------------+-------+---------+----------------+------------------------+-----------+---------------------+\n\nTotal database accesses: 32027, total allocated memory: 10624\n"
            ,
            "PIPELINED",
            0,
            1,
            1,
            "PIPELINED",
            5,
            1,
            128,
            '1',
            5.26,
            '1',
            5.26,
            1,
            2.25,
            1,
            1
        ),
        [
            new ProfiledQueryPlan(
                0,
                0,
                false,
                0,
                0,
                0.0,
                0,
                "EmptyResult@neo4j",
                new ProfiledQueryPlanArguments(
                    null,
                    null,
                    null,
                    null,
                    null,
                    0,
                    1,
                    1,
                    null,
                    null,
                    1,
                    null,
                    '',
                    null,
                    "1",
                    null,
                    1,
                    2.25,
                    '1',
                    1
                ),

                [
                    new ProfiledQueryPlan(
                        0,
                        0,
                        false,
                        0,
                        0,
                        0.0,
                        0,
                        "Create@neo4j",
                        new ProfiledQueryPlanArguments(
                            null,
                            null,
                            null,
                            null,
                            null,
                            0,
                            0,
                            0,
                            null,
                            null,
                            0,
                            null,
                            '1',
                            null,
                            '1',
                            null,
                            0,
                            2.25,
                            '1',
                            0
                        )
                    ),

                        new ProfiledQueryPlan(
                            0,
                            0,
                            false,
                            0,
                            0,
                            0.0,
                            0,
                            "Filter@neo4j",
                            new ProfiledQueryPlanArguments(
                                null,
                                null,
                                null,
                                null,
                                null,
                                0,
                                0,
                                0,
                                null,
                                0,
                                null,
                                null,
                                "cache[a.id] < cache[b.id]",
                                null,
                                "In Pipeline 3",
                                null,
                                3,
                                2.25,
                                null,
                                0
                            )
                        ),
                        [
                            new ProfiledQueryPlan(
                                0,
                                0,
                                false,
                                0,
                                0,
                                0.0,
                                0,
                                "Apply@neo4j",
                                new ProfiledQueryPlanArguments(
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    0,
                                    0,
                                    null,
                                    null,
                                    0,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    4,
                                    7.5,
                                    null,
                                    0
                                )
                            ),
                            [
                                new ProfiledQueryPlan(
                                    0,
                                    10000,
                                    false,
                                    0,
                                    0,
                                    0.0,
                                    0,
                                    "Unwind@neo4j",
                                    new ProfiledQueryPlanArguments(
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        0,
                                        null,
                                        null,
                                        null,
                                        "range(\$autoint_2, \$autoint_3) AS j",
                                        null,
                                        10,
                                        100.0,
                                        null,
                                        10000
                                    )
                                ),
                                [
                                    new ProfiledQueryPlan(
                                        0,
                                        100,
                                        false,
                                        0,
                                        0,
                                        0.0,
                                        0,
                                        "Unwind@neo4j",
                                        new ProfiledQueryPlanArguments(
                                            null,
                                            null,
                                            null,
                                            null,
                                            null,
                                            0,
                                            0,
                                            0,
                                            null,
                                            null,
                                            0,
                                            null,
                                            "range(\$autoint_0, \$autoint_1) AS i",
                                            null,
                                            "Fused in Pipeline 0",
                                            null,
                                            11,
                                            null,
                                            null,
                                            100

                                        )
                                    ),
                                    [
                                        [
                                            new ProfiledQueryPlan(
                                                0,
                                                0,
                                                false,
                                                0,
                                                0,
                                                0.0,
                                                0,
                                                "CartesianProduct@neo4j",
                                                new ProfiledQueryPlanArguments(
                                                    null,
                                                    null,
                                                    1392,
                                                    null,
                                                    null,
                                                    0,
                                                    0,
                                                    0,
                                                    null,
                                                    0,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    "In Pipeline 3",
                                                    null,
                                                    5,
                                                    7.5,
                                                    null,
                                                    0
                                                )
                                            ),

                                            [

                                                new ProfiledQueryPlan(
                                                    2027,
                                                    0,
                                                    false,
                                                    0,
                                                    0,
                                                    0.0,
                                                    0,
                                                    "Filter@neo4j",
                                                    new ProfiledQueryPlanArguments(
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        2027,
                                                        null,
                                                        "rand() < \$autodouble_4 AND cache[a.id] = i",
                                                        null,
                                                        "Fused in Pipeline 1",
                                                        null,
                                                        8,
                                                        15.0,
                                                        null,
                                                        0
                                                    )
                                                ),
                                                new ProfiledQueryPlan(
                                                    30000,
                                                    20000,
                                                    true,
                                                    10002,
                                                    0,
                                                    1.0,
                                                    6157670,
                                                    "NodeByLabelScan@neo4j",
                                                    new ProfiledQueryPlanArguments(
                                                        null,
                                                        null,
                                                        8488,
                                                        null,
                                                        null,
                                                        6157670,
                                                        0,
                                                        10002,
                                                        null,
                                                        null,
                                                        30000,
                                                        null,
                                                        "a:Person",
                                                        null,
                                                        "Fused in Pipeline 1",
                                                        null,
                                                        9,
                                                        1000.0,
                                                        null,
                                                        20000
                                                    )
                                                ),
                                                new ProfiledQueryPlan(
                                                    0,
                                                    0,
                                                    false,
                                                    0,
                                                    0,
                                                    0.0,
                                                    0,
                                                    "Filter@neo4j",
                                                    new ProfiledQueryPlanArguments(
                                                        null,
                                                        "Fused in Pipeline 2",
                                                        6,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        null,
                                                        0,
                                                        null,
                                                        "cache[b.id] = j",
                                                        null,
                                                        "Fused in Pipeline 2",
                                                        null,
                                                        6,
                                                        50.0,
                                                        null,
                                                        0
                                                    )
                                                ),

                                                new ProfiledQueryPlan(
                                                    0,
                                                    0,
                                                    false,
                                                    0,
                                                    0,
                                                    0.0,
                                                    0,
                                                    "NodeByLabelScan@neo4j",
                                                    new ProfiledQueryPlanArguments(
                                                        null,
                                                        null,
                                                        256,
                                                        null,
                                                        null,
                                                        0,
                                                        0,
                                                        0,
                                                        null,
                                                        null,
                                                        0,
                                                        null,
                                                        "b:Person",
                                                        null,
                                                        "Fused in Pipeline 2",
                                                        null,
                                                        7,
                                                        1000.0,
                                                        null,
                                                        0
                                                    )
                                                )

                                            ]

                                        ]

                                    ]
                                ]

                            ]

                        ]




                ]

            )
        ]

    )


);