<?php

namespace Neo4j\QueryAPI;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use InvalidArgumentException;
use Neo4j\QueryAPI\Exception\Neo4jException;
use Psr\Http\Client\ClientInterface;
use Neo4j\QueryAPI\Authentication\AuthenticateInterface;
use Neo4j\QueryAPI\Objects\Authentication;
use Neo4j\QueryAPI\Results\ResultSet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\RequestExceptionInterface;

class Neo4jQueryAPI
{
    public function __construct(
        private ClientInterface $client,
        private ResponseParser $responseParser,
        private Neo4jRequestFactory $requestFactory,
        private Configuration $config
    ) {

    }

    public static function login(string $address = null, ?AuthenticateInterface $auth = null, ?Configuration $config = null): self
    {
        $config = $config ?? new Configuration(baseUri: $address ?? '');
        if (
            trim($config->baseUri) !== '' &&
            $address !== null &&
            trim($address) !== '' &&
            $config->baseUri !== $address
        ) {
            throw new InvalidArgumentException(sprintf('Address (%s) as argument is different from address in configuration (%s)', $config->baseUri, $address));
        }

        $client = Psr18ClientDiscovery::find();

        return new self(
            client: $client,
            responseParser: new ResponseParser(new OGM()),
            requestFactory: new Neo4jRequestFactory(
                psr17Factory: Psr17FactoryDiscovery::findRequestFactory(),
                streamFactory: Psr17FactoryDiscovery::findStreamFactory(),
                configuration: $config,
                auth: $auth ?? Authentication::fromEnvironment()
            ),
            config: $config
        );
    }

    public static function create(Configuration $configuration, AuthenticateInterface $auth = null): self
    {
        return self::login(auth: $auth, config: $configuration);
    }


    public function getConfig(): Configuration
    {
        return $this->config;
    }


    /**
     * Executes a Cypher query.
     */
    public function run(string $cypher, array $parameters = []): ResultSet
    {
        $request = $this->requestFactory->buildRunQueryRequest($cypher, $parameters);

        try {
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            $this->handleRequestException($e);
        }

        return $this->responseParser->parseRunQueryResponse($response);
    }

    public function beginTransaction(): Transaction
    {
        $request = $this->requestFactory->buildBeginTransactionRequest();

        try {
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            $this->handleRequestException($e);
        }

        $clusterAffinity = $response->getHeaderLine('neo4j-cluster-affinity');
        $body = $response->getBody()->getContents();
        $responseData = json_decode($body, true);
        $transactionId = $responseData['transaction']['id'];

        return new Transaction(
            $this->client,
            $this->responseParser,
            $this->requestFactory,
            $clusterAffinity,
            $transactionId
        );
    }

    /**
     * Handles request exceptions by parsing error details and throwing a Neo4jException.
     *
     * @throws Neo4jException
     *
     * @return never
     */
    private function handleRequestException(RequestExceptionInterface $e): void
    {
        $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;

        if ($response instanceof ResponseInterface) {
            $errorResponse = json_decode((string)$response->getBody(), true);
            throw Neo4jException::fromNeo4jResponse($errorResponse, $e);
        }

        throw new Neo4jException(['message' => $e->getMessage()], 500, $e);
    }
}
