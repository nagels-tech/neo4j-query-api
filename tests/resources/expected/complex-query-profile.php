<?php

use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\ProfiledQueryPlanArguments;
use Neo4j\QueryAPI\Objects\ResultCounters;
use Neo4j\QueryAPI\Results\ResultSet;

return new ResultSet(
    [],
    new ResultCounters(
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
    new ProfiledQueryPlan(
        0,
        0,
        false,
        0,
        0,
        0.0,
        0,
        "ProducerResults@neo4j",
        new ProfiledQueryPlanArguments(
            10624,
            "IDP",
            0,
            "Cypher 5\n\n...",
            "PIPELINED",
            "PIPELINED",
            0,
            128,
            "",
            5.26,
            "In Pipeline 3",
            5.26,
            0,
            2.25,
            "COST",
            0,
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
                    "null",
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
                    null,
                    "In Pipeline 3",
                    null,
                    0,
                    2.25,
                    null,
                    0
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
                            "In Pipeline 3",
                            0,
                            0,
                            null,
                            0,
                            2.25,
                            0,
                            0,
                            0,
                            null,
                            "(a)-[anon_0:KNOWS]->(b), (b)-[anon_1:KNOWS]->(a)",
                            null,
                            "In Pipeline 3",
                            null,
                            2,
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
                                    0,
                                    0,
                                    0,
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
                                        0,
                                        0,
                                        0,
                                        null,
                                        0,
                                        null,
                                        "range($autoint_2, $autoint_3) AS j",
                                        null,
                                        "Fused in Pipeline 0",
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
                                            0,
                                            null,
                                            "range($autoint_0, $autoint_1) AS i",
                                            null,
                                            "Fused in Pipeline 0",
                                            null,
                                            11,
                                            10.0,
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
                                                    "In Pipeline 3",
                                                    null,
                                                    5,
                                                    7.5,
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