<?php

namespace Neo4j\QueryAPI;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class NoAuth implements AuthenticateInterface
{
    // Implement the authenticate method required by AuthenticateInterface
    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request; // No authentication, so return the original request
    }

    // Required methods from RequestInterface, implemented with defaults or basic behavior
    public function getRequestTarget(): string
    {
        return ''; // Default request target
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        return $this; // No-op for NoAuth
    }

    public function getMethod(): string
    {
        return 'GET'; // Default method
    }

    public function withMethod(string $method): RequestInterface
    {
        return $this; // No-op for NoAuth
    }

    public function getUri(): UriInterface
    {
        return new class implements UriInterface {
            public function __toString(): string
            {
                return ''; // Default URI
            }

            // Implement other methods as needed for UriInterface, or leave as empty methods
            public function getScheme(): string { return ''; }
            public function getAuthority(): string { return ''; }
            public function getUserInfo(): string { return ''; }
            public function getHost(): string { return ''; }
            public function getPort(): ?int { return null; }
            public function getPath(): string { return ''; }
            public function getQuery(): string { return ''; }
            public function getFragment(): string { return ''; }
            public function withScheme($scheme): UriInterface { return $this; }
            public function withUserInfo($user, $password = null): UriInterface { return $this; }
            public function withHost($host): UriInterface { return $this; }
            public function withPort($port): UriInterface { return $this; }
            public function withPath($path): UriInterface { return $this; }
            public function withQuery($query): UriInterface { return $this; }
            public function withFragment($fragment): UriInterface { return $this; }
        };
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        return $this; // No-op for NoAuth
    }

    public function getHeaders(): array
    {
        return []; // No headers for NoAuth
    }

    public function hasHeader(string $name): bool
    {
        return false; // No headers for NoAuth
    }

    public function getHeader(string $name): array
    {
        return []; // No headers for NoAuth
    }

    public function getHeaderLine(string $name): string
    {
        return ''; // No headers for NoAuth
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        return $this; // No-op for NoAuth
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        return $this; // No-op for NoAuth
    }

    public function withoutHeader(string $name): MessageInterface
    {
        return $this; // No-op for NoAuth
    }

    public function getBody(): StreamInterface
    {
        return new class implements StreamInterface {
            public function __toString(): string
            {
                return ''; // Default empty body
            }

            public function close(): void {}
            public function detach() {}
            public function getSize(): ?int { return 0; }
            public function tell(): int { return 0; }
            public function eof(): bool { return true; }
            public function isSeekable(): bool { return false; }
            public function seek($offset, $whence = SEEK_SET): void {}
            public function rewind(): void {}
            public function isWritable(): bool { return false; }
            public function write($string): int {}
            public function isReadable(): bool { return false; }
            public function read($length): string { return ''; }
            public function getContents(): string { return ''; }
            public function getMetadata($key = null) { return null; }
        };
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this; // No-op for NoAuth
    }

    public function getProtocolVersion(): string
    {
        return '1.1'; // Default version
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        return $this; // No-op for NoAuth
    }
}
