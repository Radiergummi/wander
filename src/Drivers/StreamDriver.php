<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Drivers;

use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Exceptions\DriverException;
use Radiergummi\Wander\Exceptions\UnresolvableHostException;
use Radiergummi\Wander\Http\Header;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Http\Status;
use Radiergummi\Wander\Wander;
use RuntimeException;

use function array_shift;
use function dns_get_record;
use function explode;
use function file_get_contents;
use function filter_var;

use const DNS_A;
use const DNS_AAAA;
use const FILTER_VALIDATE_IP;

class StreamDriver extends AbstractDriver
{

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @noinspection CallableParameterUseCaseInTypeContextInspection
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        // Replace the hostname with an IP address or bail
        $uri = $this->resolveHostname($request);
        $url = (string)$uri;

        // As we resolved the hostname to an IP address, we'll still want to
        // keep the original hostname in the Host header
        $request = $request->withHeader(
            Header::HOST,
            $request->getUri()->getHost()
        );

        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new DriverException(
                $request,
                "URL '{$url}' is not a valid URL"
            );
        }

        $options = [
            'http' => [
                'ignore_errors'    => true,
                'protocol_version' => $request->getProtocolVersion(),
                'method'           => $request->getMethod(),
                'user_agent'       => Wander::USER_AGENT,
            ],
        ];

        // If we've got a body, append it to the request
        if (
            ($bodyLength = $request->getBody()->getSize()) > 0 &&
            ! Method::mayNotIncludeBody($request->getMethod())
        ) {
            $body = $request->getBody();
            $options['http']['content'] = (string)$body;

            // Set the Content-Length header, unless already configured
            if ( ! $request->hasHeader(Header::CONTENT_LENGTH)) {
                $request = $request->withHeader(
                    Header::CONTENT_LENGTH,
                    (string)$bodyLength
                );
            }

            // Prevent errors with a missing content type
            if ( ! $request->hasHeader(Header::CONTENT_TYPE)) {
                $request = $request->withHeader(
                    Header::CONTENT_TYPE,
                    ''
                );
            }
        }

        // Set all request headers
        $options['http']['header'] = static::marshalHeaders(
            $request->getHeaders()
        );

        $context = stream_context_create($options);
        $sink = Stream::create();
        $responseBody = file_get_contents(
            $url,
            false,
            $context,
        );

        $sink->write($responseBody);

        // Rewind the stream, so we get access to it
        $sink->rewind();

        /**
         * `$http_response_header` is a magic variable, created after performing
         * a stream request. It contains the full header section, including the
         * status line, which we shift off here so the rest of the array will be
         * the raw response headers only.
         *
         * @var array<array-key, string>
         */
        $responseHeaders = $http_response_header;

        $statusLine = array_shift($responseHeaders);

        preg_match(
            '{HTTP/([\d.]+)\S*\s(\d{3})(\s(.+))?}',
            $statusLine,
            $match
        );

        // The leading comma is deliberate, as we want to discard the full match
        [, $protocolVersion, $statusCode, $reasonPhrase] = $match + [
            null,
            '',
            '',
            '',
            '',
            '',
        ];

        if ( ! $reasonPhrase) {
            $reasonPhrase = Status::getMessage((int)$statusCode);
        }

        $response = $this
            ->getResponseFactory()
            ->createResponse((int)$statusCode, $reasonPhrase)
            ->withProtocolVersion($protocolVersion)
            ->withBody($sink);

        // Set all response headers
        foreach ($responseHeaders as $headerLine) {
            [$name, $value] = explode(':', (string)$headerLine);

            $response = $response->withHeader(
                $name,
                trim($value)
            );
        }

        return $response;
    }

    /**
     * Resolves the host from a URI to its IP address or throws if the record
     * can't be found
     *
     * @param RequestInterface $request
     *
     * @return UriInterface
     * @throws InvalidArgumentException
     * @throws UnresolvableHostException
     */
    private function resolveHostname(RequestInterface $request): UriInterface
    {
        $uri = $request->getUri();

        // If the hostname is an IP address already, we skip DNS resolution
        if (filter_var($uri->getHost(), FILTER_VALIDATE_IP)) {
            return $uri;
        }

        // Fetch the A and AAAA records for the host
        $records = dns_get_record(
            $uri->getHost(),
            DNS_A + DNS_AAAA
        );

        // If we don't have a record, bail out
        if ($records === false || ! isset($records[0]['ip'])) {
            throw new UnresolvableHostException($uri);
        }

        return $uri->withHost((string)$records[0]['ip']);
    }
}
