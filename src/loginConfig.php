<?php

namespace Neo4j\QueryAPI;

class loginConfig
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $authToken
    ) {
    }

    public static function fromEnv(): self
    {
        return new self(
            baseUrl: getenv('NEO4J_ADDRESS'),
            authToken: base64_encode(getenv('NEO4J_USERNAME') . ':' . getenv('NEO4J_PASSWORD'))
        );
    }


}
