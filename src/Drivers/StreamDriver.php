<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Drivers;

use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Radiergummi\Wander\Exceptions\DriverException;
use Radiergummi\Wander\Exceptions\ResponseErrorException;
use Radiergummi\Wander\Http\Header;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Http\Status;
use RuntimeException;

use function array_shift;
use function explode;
use function file_get_contents;

class StreamDriver extends AbstractDriver
{
    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $url = (string)$request->geturi();

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new DriverException($request, "URL '{$url}' is not a valid URL");
        }

        $options = [
            'http' => [
                'ignore_errors' => true,
                'protocol_version' => $request->getProtocolVersion(),
                'method' => $request->getMethod(),
                'user_agent' => 'wander/1.0',
            ],
        ];

        // If we've got a body, append it to the request
        if (
            ($bodyLength = $request->getBody()->getSize()) > 0 &&
            !Method::mayNotIncludeBody($request->getMethod())
        ) {
            $body = $request->getBody();
            $options['content'] = static::detach($body);

            // Set the Content-Length header, unless already configured
            if (!$request->hasHeader(Header::CONTENT_LENGTH)) {
                /**
                 * This is necessary due to PHPStorm detecting--correctly so--that
                 * the helper methods return a MessageInterface instance. I would
                 * have wished they'd just annotated the methods with `@return $this`
                 * or set the type to `: self`, but alas, PHP-FIG didn't, so we have
                 * a false positive.
                 *
                 * @noinspection CallableParameterUseCaseInTypeContextInspection
                 */
                $request = $request->withHeader(
                    Header::CONTENT_LENGTH,
                    (string)$bodyLength
                );
            }
        }

        // Set all request headers
        $options['header'] = static::marshalHeaders($request->getHeaders());

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
         * `$http_response_header` is a magic variable, created after performing a
         * stream request. It contains the full header section, including the status
         * line, which we shift off here so the rest of the array will be the raw
         * response headers only.
         *
         * @var array<array-key, string>
         */
        $responseHeaders = $http_response_header;

        $statusLine = array_shift($responseHeaders);

        preg_match(
            '{HTTP/([\d.]+)\S*\s(\d{3})\s(.+)}',
            $statusLine,
            $match
        );

        // The leading comma is deliberate, as we want to discard the full match
        [, $protocolVersion, $statusCode, $reasonPhrase] = $match;

        $response = $this
            ->getResponseFactory()
            ->createResponse((int)$statusCode, $reasonPhrase)
            ->withProtocolVersion($protocolVersion)
            ->withBody($sink);

        // Set all response headers
        foreach ($responseHeaders as $headerLine) {
            [$name, $value] = explode(':', (string)$headerLine);

            $response = $response->withHeader($name, trim($value));
        }

        // If the status code is from the error ranges, we throw to indicate the
        // request has failed. As the exception contains the response instance, users
        // get to work with the response if they are inclined to.
        if (Status::isError((int)$statusCode)) {
            throw new ResponseErrorException(
                $request,
                $response
            );
        }

        return $response;
    }
}
