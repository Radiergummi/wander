<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Context;

use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Exceptions;
use Radiergummi\Wander\Http\Authorization;
use Radiergummi\Wander\Http\Header;
use Radiergummi\Wander\Http\MediaType;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use Radiergummi\Wander\Serializers\PlainTextSerializer;

use function base64_encode;
use function http_build_query;
use function parse_str;

class RequestContext
{
    use ContextTrait;

    protected HttpClientInterface $client;

    protected RequestInterface $request;

    /**
     * Holds unserialized request body data.
     *
     * @var mixed|null
     */
    protected $body = null;

    /**
     * @param HttpClientInterface $client  HTTP client instance.
     * @param RequestInterface    $request HTTP request to wrap.
     *
     * @internal
     */
    public function __construct(
        HttpClientInterface $client,
        RequestInterface $request
    ) {
        $this->client = $client;
        $this->request = $request;
    }

    /**
     * Allows to override the request instance.
     *
     * @param RequestInterface $request New request instance.
     */
    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * Retrieves the current request instance.
     *
     * @return RequestInterface Current request instance.
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Sets the HTTP request method. Note that all non-empty strings are valid
     * request methods.
     *
     * @param string $method Request method to use.
     *
     * @return $this
     * @throws InvalidArgumentException For invalid request methods.
     */
    public function withMethod(string $method): self
    {
        $this->request = $this->request->withMethod($method);

        return $this;
    }

    /**
     * Retrieves the HTTP request method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * Sets the request URI.
     *
     * @param UriInterface $uri
     * @param bool         $preserveHost
     *
     * @return $this
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
     * Retrieves the request URI.
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    /**
     * Sets the query string.
     *
     * @param string $queryString
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withQueryString(string $queryString): self
    {
        $uri = $this->getUri()->withQuery($queryString);

        return $this->withUri($uri);
    }

    /**
     * Retrieves the query string.
     *
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->getUri()->getQuery();
    }

    /**
     * Sets all query parameters on the request URI.
     *
     * @param mixed[] $queryParameters Query parameters as a dictionary.
     *
     * @psalm-param array<array-key, scalar> $queryParameters
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withQueryParameters(array $queryParameters): self
    {
        $queryString = $this->encodeQueryParameters(
            $queryParameters
        );

        return $this->withQueryString($queryString);
    }

    /**
     * Retrieves all query parameters as a dictionary.
     *
     * @return string[]
     *
     * @psalm-return array<array-key, string>
     */
    public function getQueryParameters(): array
    {
        $queryString = $this->getQueryString();

        return $this->decodeQueryParameters($queryString);
    }

    /**
     * Sets a single query parameter.
     *
     * @param string $name  Name of the query parameter to set.
     * @param mixed  $value Value of the query parameter. Must be scalar or able
     *                      to convert to a string.
     *
     * @psalm-param scalar $value
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withQueryParameter(string $name, $value): self
    {
        $queryParameters = $this->getQueryParameters();

        // Set the new parameter value. We _could_ handle appending parameters
        // multiple times here, as in, creating an array for any existing
        // parameter.
        $queryParameters[$name] = $value;

        return $this->withQueryParameters($queryParameters);
    }

    /**
     * Removes a single query parameter by name.
     *
     * @param string $name Name of the parameter to remove.
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withoutQueryParameter(string $name): self
    {
        $queryParameters = $this->getQueryParameters();

        unset($queryParameters[$name]);

        return $this->withQueryParameters($queryParameters);
    }

    /**
     * Retrieves a single query parameter by name. If the query parameter does
     * not exist in the query string yet, `NULL` will be returned.
     *
     * @param string $name Name of the parameter to retrieve.
     *
     * @return string|null Value of the parameter if it is set, `NULL`
     *                     otherwise.
     */
    public function getQueryParameter(string $name): ?string
    {
        $queryParameters = $this->getQueryParameters();

        return $queryParameters[$name] ?? null;
    }

    /**
     * Sets multiple headers at once.
     *
     * @param array<string, string> $headers Headers as a dictionary of header
     *                                       names to values.
     * @param bool                  $append  Whether to append values to
     *                                       pre-existing headers.
     *
     * @return $this
     * @throws InvalidArgumentException For invalid header names or values.
     */
    public function withHeaders(array $headers, bool $append = false): self
    {
        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value, $append);
        }

        return $this;
    }

    /**
     * If `append` is `TRUE`, the provided value will replace the specified
     * header if it already exists. Otherwise, existing values for the specified
     * header will be maintained. The new value(s) will be appended to the
     * existing list. If the header did not exist previously, it will be added.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * @param string          $name   Case-insensitive name of the header
     * @param string|string[] $value  Header value
     * @param bool            $append Whether to append values to pre-existing
     *                                headers
     *
     * @return $this
     * @throws InvalidArgumentException For invalid header names or values.
     */
    public function withHeader(
        string $name,
        $value,
        bool $append = false
    ): self {
        $this->request = $append
            ? $this->request->withAddedHeader($name, $value)
            : $this->request->withHeader($name, $value);

        return $this;
    }

    /**
     * Removes the specified header.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return $this
     */
    public function withoutHeader(string $name): self
    {
        $this->request = $this->request->withoutHeader($name);

        return $this;
    }

    /**
     * Shorthand method to set the Authorization header. While its technically
     * rather "withAuthentication", I've decided to go with the way the header
     * is named.
     *
     * @param string $type
     * @param string $credentials
     *
     * @psam-param Authorization::* $type
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withAuthorization(
        string $type,
        string $credentials
    ): self {
        return $this->withHeader(
            Header::AUTHORIZATION,
            "{$type} {$credentials}"
        );
    }

    /**
     * Shorthand method to set basic authentication
     *
     * @param string      $username
     * @param string|null $password
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withBasicAuthorization(
        string $username,
        ?string $password
    ): self {
        return $this->withAuthorization(
            Authorization::BASIC,
            base64_encode("{$username}:{$password}")
        );
    }

    /**
     * Shorthand method to set bearer authentication
     *
     * @param string $token
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withBearerAuthorization(
        string $token
    ): RequestContext {
        return $this->withAuthorization(
            Authorization::BEARER,
            $token
        );
    }

    /**
     * Shorthand method to set the Content-Type header. The content type also
     * affects body serialization.
     *
     * @param string $contentType
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withContentType(string $contentType): self
    {
        return $this->withHeader(
            Header::CONTENT_TYPE,
            $contentType
        );
    }

    /**
     * Shorthand method to set the Content-Type header to application/json
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function asJson(): self
    {
        return $this->withHeader(
            Header::CONTENT_TYPE,
            MediaType::APPLICATION_JSON
        );
    }

    /**
     * Shorthand method to set the Content-Type header to text/xml
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function asXml(): self
    {
        return $this->withHeader(
            Header::CONTENT_TYPE,
            MediaType::TEXT_XML
        );
    }

    /**
     * Shorthand method to set the Content-Type header to text/plain
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function asPlainText(): self
    {
        return $this->withHeader(
            Header::CONTENT_TYPE,
            MediaType::TEXT_PLAIN
        );
    }

    /**
     * Sets the request body. Data may be passed with any type, as
     * serialization happens just before actually dispatching the
     * request. This allows to set the content encoding separately
     * or re-define it conditionally.
     *
     * @param mixed|null $body
     *
     * @return $this
     */
    public function withBody($body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Retrieves the body instance
     *
     * @return mixed|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Checks whether a body is set
     *
     * @return bool
     */
    public function hasBody(): bool
    {
        return isset($this->body);
    }

    /**
     * Runs the underlying request.
     *
     * @return ResponseContext
     * @throws Exceptions\ConnectionException
     * @throws Exceptions\ResponseErrorException
     * @throws Exceptions\SslCertificateException
     * @throws Exceptions\UnresolvableHostException
     * @throws Exceptions\WanderException
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     */
    public function run(): ResponseContext
    {
        // If we don't have a body, request right away
        if ( ! $this->hasBody()) {
            $response = $this->client->request($this->request);

            return new ResponseContext(
                $this->client,
                $response
            );
        }

        /** @var mixed $body */
        $body = $this->getBody();

        // If we have a stream body already, we can simply pass that to the
        // request directly. The user must have serialized it manually already.
        if ($body instanceof StreamInterface) {
            /** @var RequestInterface $request */
            $request = $this
                ->getMessage()
                ->withBody($body);

            $this->setRequest($request);

            $response = $this->client->request($request);

            return new ResponseContext(
                $this->client,
                $response
            );
        }

        // Resolve the appropriate serializer by resolving the media type from
        // the current request instance.
        $contentType = $this->getContentType(true)
                       ?? MediaType::TEXT_PLAIN;

        $serializer = $this->client
                          ->getSerializerRegistry()
                          ->resolve($contentType)
                      ?? new PlainTextSerializer();

        // Let the serializer apply the body to the request. Passing the request
        // makes it possible to alter headers, depending on the serialization
        // method (eg. multipart).
        $request = $serializer->apply(
            $this->request,
            $body
        );

        // Set the modified request instance for later reference
        $this->setRequest($request);

        $response = $this->client->request($request);

        return new ResponseContext($this->client, $response);
    }

    /**
     * Encodes an associative array of query parameters into a query string
     *
     * @param mixed[] $queryParameters Query parameters as a dictionary
     *
     * @psalm-param array<array-key, scalar> $queryParameters
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
     * @return string[] Query parameters as a dictionary
     *
     * @psalm-return   array<array-key, string>
     * @psalm-suppress MixedReturnTypeCoercion Because psalm is wrong here.
     *                                         `parse_str` can only infer scalar
     *                                         types anyway.
     */
    private function decodeQueryParameters(string $queryString): array
    {
        $queryParameters = [];

        // Parse the query string, put the resulting data into query
        parse_str($queryString, $queryParameters);

        return $queryParameters;
    }

    /**
     * @inheritDoc
     */
    final protected function getMessage(): MessageInterface
    {
        return $this->request;
    }
}
