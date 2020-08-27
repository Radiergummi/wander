<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Interfaces;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Radiergummi\Wander\Exceptions\ConnectionException;
use Radiergummi\Wander\Exceptions\ResponseErrorException;
use Radiergummi\Wander\Exceptions\SslCertificateException;
use Radiergummi\Wander\Exceptions\UnresolvableHostException;
use Radiergummi\Wander\Exceptions\WanderException;

interface HttpClientInterface
{
    /**
     * Retrieves the dictionary of supported media types.
     * This MUST return an array that maps media types to
     * fully-qualified body class paths.
     *
     * @return array<string, class-string<SerializerInterface>>
     */
    public function getBodySerializers(): array;

    /**
     * Handles a request and returns the response.
     *
     * @param RequestInterface $request Request to handle
     *
     * @return ResponseInterface         HTTP response as received from the
     *                                   remote server.
     * @throws ResponseErrorException    If the remote server returned a
     *                                   response with a non-successful status
     *                                   code (non-2xx).
     * @throws SslCertificateException    If the SSL certificate of the remote
     *                                   host is invalid or broken or otherwise
     *                                   prevents the request from being carried
     *                                   out. Note that the verification of SSL
     *                                   certificates can be disabled on
     *                                   the client.
     * @throws UnresolvableHostException If the hostname cannot be resolved to a
     *                                   valid IP address.
     * @throws ConnectionException       If a connection to the remote server
     *                                   cannot be established or fails due to
     *                                   communication issues. This might also
     *                                   be a local problem.
     * @throws ClientExceptionInterface  If the client experienced an error.
     * @throws WanderException           If an unexpected error occurs during
     *                                   the request. This hints at a bug in
     *                                   the library.
     */
    public function request(RequestInterface $request): ResponseInterface;
}
