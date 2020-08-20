<?php

declare(strict_types=1);

namespace Radiergummi\Wander;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Http\Header;
use Radiergummi\Wander\Http\MediaType;
use Radiergummi\Wander\Interfaces\BodyInterface;
use Radiergummi\Wander\Interfaces\ContextInterface;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use UnEncodedBody;

use function http_build_query;
use function parse_str;

class Context implements ContextInterface
{
    protected HttpClientInterface $client;

    protected RequestInterface $request;

    protected ?BodyInterface $body = null;

    /**
     * @param HttpClientInterface $client  HTTP client instance
     * @param RequestInterface    $request HTTP request to wrap
     */
    public function __construct(
        HttpClientInterface $client,
        RequestInterface $request
    ) {
        $this->client = $client;
        $this->request = $request;
    }

    /**
     * Allows to override the request instance
     *
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function withMethod(string $method): self
    {
        $this->request = $this->request->withMethod($method);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): self
    {
        $this->request = $this->request->withUri(
            $uri,
            $preserveHost
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    /**
     * @inheritDoc
     */
    public function withQueryString(string $queryString): self
    {
        $uri = $this->getUri()->withQuery($queryString);

        return $this->withUri($uri);
    }

    /**
     * @inheritDoc
     */
    public function getQueryString(): string
    {
        return $this->getUri()->getQuery();
    }

    /**
     * @inheritDoc
     */
    public function withQueryParameters(array $queryParameters): self
    {
        $queryString = $this->encodeQueryParameters($queryParameters);

        return $this->withQueryString($queryString);
    }

    /**
     * @inheritDoc
     */
    public function getQueryParameters(): array
    {
        $queryString = $this->getQueryString();

        return $this->decodeQueryParameters($queryString);
    }

    /**
     * @inheritDoc
     */
    public function withQueryParameter(string $name, $value): self
    {
        $queryParameters = $this->getQueryParameters();

        // Set the new parameter value. We _could_ handle appending parameters
        // multiple times here, as in, creating an array for any existing parameter.
        $queryParameters[$name] = $value;

        return $this->withQueryParameters($queryParameters);
    }

    /**
     * @inheritDoc
     */
    public function withoutQueryParameter(string $name): self
    {
        $queryParameters = $this->getQueryParameters();

        unset($queryParameters[$name]);

        return $this->withQueryParameters($queryParameters);
    }

    /**
     * @inheritDoc
     */
    public function getQueryParameter(string $name)
    {
        $queryParameters = $this->getQueryParameters();

        return $queryParameters[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function withHeaders(array $headers, bool $append = false): self
    {
        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value, $append);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    /**
     * @inheritDoc
     */
    public function withHeader(
        string $name,
        string $value,
        bool $append = false
    ): self {
        $this->request = $append
            ? $this->request->withAddedHeader($name, $value)
            : $this->request->withHeader($name, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name): self
    {
        $this->request = $this->request->withoutHeader($name);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): array
    {
        return $this->request->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine(string $name): string
    {
        return $this->request->getHeaderLine($name);
    }

    /**
     * @inheritDoc
     */
    public function withContentType(string $contentType): self
    {
        return $this->withHeader(Header::CONTENT_TYPE, $contentType);
    }

    /**
     * @inheritDoc
     */
    public function asJson(): self
    {
        return $this->withHeader(
            Header::CONTENT_TYPE,
            MediaType::APPLICATION_JSON
        );
    }

    /**
     * @inheritDoc
     */
    public function asXml(): self
    {
        return $this->withHeader(
            Header::CONTENT_TYPE,
            MediaType::TEXT_XML
        );
    }

    /**
     * @inheritDoc
     */
    public function asPlainText(): self
    {
        return $this->withHeader(
            Header::CONTENT_TYPE,
            MediaType::TEXT_PLAIN
        );
    }

    /**
     * @inheritDoc
     */
    public function withBody($body): self
    {
        // Setting the body to NULL should do what users expect - remove the body
        if ($body === null) {
            $this->body = null;

            return $this;
        }

        // If the passed body is not a body instance yet, we store it as a temporary
        // unencoded body. That allows us to define the content encoding later on.
        if (! ($body instanceof BodyInterface)) {
            $body = new UnEncodedBody($body);
        }

        $this->body = $body;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): ?BodyInterface
    {
        return $this->body;
    }

    /**
     * Runs the request. This is actually a shorthand that passes the request
     * instance to the client.
     *
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    public function run(): ResponseInterface
    {
        // TODO: Set serialized body depending on content type

        return $this->client->request($this->request);
    }

    /**
     * Encodes an associative array of query parameters into a query string
     *
     * @param array<string, mixed> $queryParameters Query parameters as a dictionary
     *
     * @return string Query string
     */
    private function encodeQueryParameters(array $queryParameters): string
    {
        return http_build_query($queryParameters);
    }

    /**
     * Decodes a query string into an associative array
     *
     * @param string $queryString Query string
     *
     * @return array<string, string> Query parameters as a dictionary
     */
    private function decodeQueryParameters(string $queryString): array
    {
        $queryParameters = [];

        // Parse the query string, put the resulting data into query
        parse_str($queryString, $queryParameters);

        return $queryParameters;
    }
}
