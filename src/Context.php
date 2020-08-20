<?php

declare(strict_types=1);

namespace Radiergummi\Wander;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Http\Authorization;
use Radiergummi\Wander\Http\Header;
use Radiergummi\Wander\Http\MediaType;
use Radiergummi\Wander\Interfaces\BodyInterface;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use UnEncodedBody;

use function base64_encode;
use function http_build_query;
use function parse_str;

class Context
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
     * Retrieves the current request instance
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Sets the HTTP request method
     *
     * @param string $method
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withMethod(string $method): self
    {
        $this->request = $this->request->withMethod($method);

        return $this;
    }

    /**
     * Retrieves the HTTP request method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * Sets the request URI
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
     * Retrieves the request URI
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    /**
     * Sets the query string
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
     * Retrieves the query string
     *
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->getUri()->getQuery();
    }

    /**
     * Sets all query parameters on the request URI
     *
     * @param array<array-key, string|int|float|bool|null> $queryParameters
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withQueryParameters(array $queryParameters): self
    {
        $queryString = $this->encodeQueryParameters($queryParameters);

        return $this->withQueryString($queryString);
    }

    /**
     * Retrieves all query parameters
     *
     * @return array<array-key, string|int|float|bool|null>
     */
    public function getQueryParameters(): array
    {
        $queryString = $this->getQueryString();

        return $this->decodeQueryParameters($queryString);
    }

    /**
     * Sets a single query parameter
     *
     * @param string                     $name
     * @param string|int|float|bool|null $value
     *
     * @return $this
     * @throws InvalidArgumentException
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
     * Removes a single query parameter
     *
     * @param string $name
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
     * Retrieves a single query parameter by name
     *
     * @param string $name Name of the parameter to retrieve
     *
     * @return string|int|float|bool|null Value of the parameter if it is set, `NULL`
     *                                    otherwise
     */
    public function getQueryParameter(string $name)
    {
        $queryParameters = $this->getQueryParameters();

        return $queryParameters[$name] ?? null;
    }

    /**
     * Sets multiple headers at once.
     *
     * @param array<string, string> $headers Headers as a dictionary of header names
     *                                       to values
     * @param bool                  $append  Whether to append values to pre-existing
     *                                       headers
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withHeaders(array $headers, bool $append = false): self
    {
        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value, $append);
        }

        return $this;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *                    key MUST be a header name, and each value MUST be an array
     *                    of strings for that header.
     */
    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    /**
     * If `append` is `TRUE`, the provided value will replace the specified header if
     * it already exists. Otherwise, existing values for the specified header will be
     * maintained. The new value(s) will be appended to the existing list. If the
     * header did not exist previously, it will be added.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * @param string $name   Case-insensitive name of the header
     * @param string $value  Header value
     * @param bool   $append Whether to append values to pre-existing headers
     *
     * @return $this
     * @throws InvalidArgumentException For invalid header names or values.
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
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given header.
     *                  If the header does not appear in the message, this method
     *                  MUST return an empty array.
     */
    public function getHeader(string $name): array
    {
        return $this->request->getHeader($name);
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given case-insensitive
     * header name as a string concatenated together using a comma.
     *
     * NOTE: Not all header values may be appropriately represented using comma
     * concatenation. For such headers, use getHeader() instead and supply your own
     * delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty string.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header
     *                concatenated together using a comma. If the header does not
     *                appear in the message, this method MUST return an empty string.
     */
    public function getHeaderLine(string $name): string
    {
        return $this->request->getHeaderLine($name);
    }

    /**
     * Shorthand method to set the Authorization header. While its technically rather
     * "withAuthentication", I've decided to go with the way the header is named.
     *
     * @param string $type
     * @param string $credentials
     *
     * @psam-param Authorization::* $type
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withAuthorization(string $type, string $credentials): self
    {
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
    ): Context {
        return $this->withAuthorization(
            Authorization::BEARER,
            $token
        );
    }

    /**
     * Shorthand method to set the Content-Type header. The content type also affects
     * body serialization.
     *
     * @param string $contentType
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withContentType(string $contentType): self
    {
        return $this->withHeader(Header::CONTENT_TYPE, $contentType);
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
     * Sets the request body. Data may be passed with any type, as serialization
     * happens just before actually dispatching the request. This allows to set the
     * content encoding separately or re-define it conditionally.
     *
     * @param mixed $body
     *
     * @return $this
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
     * Retrieves the body instance
     *
     * @return BodyInterface|null
     */
    public function getBody(): ?BodyInterface
    {
        return $this->body;
    }

    /**
     * Runs the underlying request.
     *
     * @return ResponseInterface
     * @throws Exceptions\ConnectionException
     * @throws Exceptions\ResponseErrorException
     * @throws Exceptions\SslCertificateException
     * @throws Exceptions\UnresolvableHostException
     * @throws Exceptions\WanderException
     */
    public function run(): ResponseInterface
    {
        // TODO: Set serialized body depending on content type

        return $this->client->request($this->request);
    }

    /**
     * Encodes an associative array of query parameters into a query string
     *
     * @param array<array-key, mixed> $queryParameters Query parameters as a dictionary
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
     * @return array<array-key, string|int|float|bool|null> Query parameters as a
     *                                                      dictionary
     *
     * @psalm-suppress MixedReturnTypeCoercion Because psalm is wrong here. parse_str
     *                                         can only infer scalar types.
     */
    private function decodeQueryParameters(string $queryString): array
    {
        $queryParameters = [];

        // Parse the query string, put the resulting data into query
        parse_str($queryString, $queryParameters);

        return $queryParameters;
    }
}
