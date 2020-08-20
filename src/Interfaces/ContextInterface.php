<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Interfaces;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

interface ContextInterface
{
    /**
     * Allows to override the request instance
     *
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request): void;

    /**
     * Retrieves the current request instance
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;

    /**
     * Sets the HTTP request method
     *
     * @param string $method
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withMethod(string $method): self;

    /**
     * Retrieves the HTTP request method
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Sets the URI
     *
     * @param UriInterface $uri
     * @param bool         $preserveHost
     *
     * @return $this
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): self;

    /**
     * Retrieves the request URI
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface;

    /**
     * Sets the query string
     *
     * @param string $queryString
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withQueryString(string $queryString): self;

    /**
     * Retrieves the query string
     *
     * @return string
     */
    public function getQueryString(): string;

    /**
     * Sets all query parameters on the request URI
     *
     * @param array $queryParameters
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withQueryParameters(array $queryParameters): self;

    /**
     * Retrieves all query parameters
     *
     * @return array
     */
    public function getQueryParameters(): array;

    /**
     * Sets a single query parameter
     *
     * @param string $name
     * @param        $value
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withQueryParameter(string $name, $value): self;

    /**
     * Removes a single query parameter
     *
     * @param string $name
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withoutQueryParameter(string $name): self;

    /**
     * Retrieves a single query parameter by name
     *
     * @param string $name Name of the parameter to retrieve
     *
     * @return mixed|null Value of the parameter if it is set, `NULL` otherwise
     */
    public function getQueryParameter(string $name);

    /**
     * Sets multiple headers at once.
     *
     * @param array $headers Headers as a dictionary of header names to values
     * @param bool  $append  Whether to append values to pre-existing headers
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withHeaders(array $headers, bool $append = false): self;

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
    public function getHeaders(): array;

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
    ): self;

    /**
     * Removes the specified header.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return $this
     */
    public function withoutHeader(string $name): self;

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
    public function getHeader(string $name): array;

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
    public function getHeaderLine(string $name): string;

    /**
     * Shorthand method to set the Content-Type header. The content type also affects
     * body serialization.
     *
     * @param string $contentType
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withContentType(string $contentType): self;

    /**
     * Shorthand method to set the Content-Type header to application/json
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function asJson(): self;

    /**
     * Shorthand method to set the Content-Type header to text/xml
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function asXml(): self;

    /**
     * Shorthand method to set the Content-Type header to text/plain
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function asPlainText(): self;

    /**
     * Sets the request body. Data may be passed with any type, as serialization
     * happens just before actually dispatching the request. This allows to set the
     * content encoding separately or re-define it conditionally.
     *
     * @param mixed $body
     *
     * @return $this
     */
    public function withBody($body): self;

    /**
     * Retrieves the body instance
     *
     * @return BodyInterface|null
     */
    public function getBody(): ?BodyInterface;

    /**
     * Runs the underlying request.
     *
     * @return ResponseInterface
     */
    public function run(): ResponseInterface;
}
