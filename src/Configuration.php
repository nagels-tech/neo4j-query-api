<?php

namespace Neo4j\QueryAPI;

class Configuration
{
    private string $baseUrl;
    private string $authToken;
    private array $defaultHeaders;

    public function __construct(
        string $baseUrl = 'https://localhost:7474',
        string $authToken = '',
        array $defaultHeaders = []
    ) {
        $this->baseUrl = $baseUrl;
        $this->authToken = $authToken;
        $this->defaultHeaders = $defaultHeaders;
    }

    /**
     * Set the base URL of the API.
     *
     * @param string $baseUrl
     * @return self
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Set the authentication token.
     *
     * @param string $authToken
     * @return self
     */
    public function setAuthToken(string $authToken): self
    {
        $this->authToken = $authToken;
        return $this;
    }

    /**
     * Set default headers for API requests.
     *
     * @param array $headers
     * @return self
     */
    public function setDefaultHeaders(array $headers): self
    {
        $this->defaultHeaders = $headers;
        return $this;
    }

    /**
     * Get the base URL of the API.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the authentication token.
     *
     * @return string
     */
    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    /**
     * Get the default headers for API requests.
     *
     * @return array
     */
    public function getDefaultHeaders(): array
    {
        return array_merge($this->defaultHeaders, [
            'Authorization' => 'Bearer ' . $this->authToken,
            'Content-Type' => 'application/json',
        ]);
    }
}
