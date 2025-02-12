<?php

namespace Neo4j\QueryAPI\Enums;

enum AccessMode: string
{
    case READ = 'READ';
    case WRITE = 'WRITE';
}
