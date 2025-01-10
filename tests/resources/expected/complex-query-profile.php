<?php

use Neo4j\QueryAPI\Objects\ProfiledQueryPlan;
use Neo4j\QueryAPI\Objects\QueryArguments;
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
        new QueryArguments(
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
                new QueryArguments(
                    pipelineInfo: "In Pipeline 3",
                    time:0,

                )
            )
        ]
    )

);