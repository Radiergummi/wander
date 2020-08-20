<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Drivers;

use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Radiergummi\Wander\Exceptions\ClientException;
use Radiergummi\Wander\Exceptions\ConnectionException;
use Radiergummi\Wander\Exceptions\SslCertificateException;
use Radiergummi\Wander\Exceptions\UnresolvableHostException;
use Radiergummi\Wander\Http\Header;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Http\Status;
use RuntimeException;

use function count;
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function explode;
use function sprintf;
use function strlen;
use function strtolower;
use function trim;

use const CURLE_COULDNT_CONNECT;
use const CURLE_COULDNT_RESOLVE_HOST;
use const CURLE_FAILED_INIT;
use const CURLE_GOT_NOTHING;
use const CURLE_READ_ERROR;
use const CURLE_RECV_ERROR;
use const CURLE_SSL_CACERT;
use const CURLE_SSL_CACERT_BADFILE;
use const CURLE_SSL_CERTPROBLEM;
use const CURLE_SSL_CIPHER;
use const CURLE_SSL_CONNECT_ERROR;
use const CURLE_SSL_ENGINE_NOTFOUND;
use const CURLE_SSL_ENGINE_SETFAILED;
use const CURLE_SSL_PEER_CERTIFICATE;
use const CURLE_SSL_PINNEDPUBKEYNOTMATCH;
use const CURLE_TOO_MANY_REDIRECTS;
use const CURLINFO_HTTP_CODE;
use const CURLINFO_HTTP_VERSION;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_FAILONERROR;
use const CURLOPT_HEADERFUNCTION;
use const CURLOPT_HTTP200ALIASES;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_INFILE;
use const CURLOPT_INFILESIZE;
use const CURLOPT_POST;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_UPLOAD;
use const CURLOPT_URL;
use const CURLOPT_WRITEFUNCTION;

/**
 * Curl Driver
 * ===========
 * A driver implementation that uses curl to make requests
 *
 * @package Radiergummi\Wander\Drivers
 * @author  Moritz Friedrich <m@9dev.de>
 * @license MIT
 */
class CurlDriver extends AbstractDriver
{
    private ?array $defaultOptions;

    /**
     * @param array<int, string>|null $curlOptions
     */
    public function __construct(?array $curlOptions = null)
    {
        $this->defaultOptions = $curlOptions;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        // Create a curl handle
        $handle = curl_init();
        $options = $this->getDefaultOptions() ?? [];

        // Set the URL
        $options[CURLOPT_URL] = (string)$request->getUri();

        // Alias *all* HTTP error codes as 200 and work them out on the client
        $options[CURLOPT_HTTP200ALIASES] = Status::getErrorCodes();
        $options[CURLOPT_FAILONERROR] = false;

        // If we've got a body, append it to the request
        if (($bodyLength = $request->getBody()->getSize()) > 0) {
            $options[CURLOPT_INFILE] = static::detach($request->getBody());
            $options[CURLOPT_INFILESIZE] = $bodyLength;

            // CURLOPT_UPLOAD makes the request a streaming upload - and sets the
            // request method to PUT as an unwanted side effect, so we'll need to
            // set it below
            $options[CURLOPT_UPLOAD] = true;

            // Set the Content-Length header, unless already configured
            if (! $request->hasHeader(Header::CONTENT_LENGTH)) {
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

        // Handle the HTTP method correctly
        switch ($request->getMethod()) {
            case Method::GET:
                break;

            case Method::POST:
                $options[CURLOPT_POST] = true;
                break;

            /**
             * Theoretically, curl supports a PUT option (CURLOPT_PUT) for PUT
             * requests. The way its implemented, though, is incompatible with most
             * web servers: It sends an `Expect: 100 Continue` header before actually
             * submitting the request. To prevent this problem, PUT has been added to
             * the custom request block below, which works just fine.
             */
            // case Method::PUT:
            //     $options[CURLOPT_PUT] = true;
            //     break;

            // All other methods must use CURLOPT_CUSTOMREQUEST
            case Method::PUT:
            case Method::HEAD:
            case Method::DELETE:
            case Method::PATCH:
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        }

        // TODO: Set as default if no timeouts provided
        // $options[CURLOPT_TIMEOUT] = self::CURL_TIMEOUT;
        // $options[CURLOPT_CONNECTTIMEOUT] = self::CURL_CONNECT_TIMEOUT;

        // Set all request headers
        $options[CURLOPT_HTTPHEADER] = self::marshalHeaders(
            $request->getHeaders()
        );

        // Make sure curl keeps the response body
        $options[CURLOPT_RETURNTRANSFER] = false;

        /**
         * Holds all response headers
         *
         * @var string[][]
         */
        $responseHeaders = [];

        /**
         * The header function is run by curl to process the response headers
         *
         * @param resource $handle
         * @param string   $header
         *
         * @return int
         * @psalm-suppress MissingClosureParamType Because of a psalm bug I already
         *                                         filed an issue for
         * @see            https://github.com/vimeo/psalm/issues/4033
         */
        $options[CURLOPT_HEADERFUNCTION] = static function (
            $handle,
            string $header
        ) use (&$responseHeaders): int {
            /** @var array<string,string[]> $responseHeaders */

            $length = strlen($header);
            $parts = explode(':', $header, 2);

            if (count($parts) < 2) {
                return $length;
            }

            $name = strtolower(trim($parts[0]));

            if (! isset($responseHeaders[$name])) {
                $responseHeaders[$name] = [];
            }

            $responseHeaders[$name][] = trim($parts[1]);

            return $length;
        };

        $sink = Stream::create();

        /**
         * @param resource $handle
         * @param string   $chunk
         *
         * @return int
         * @psalm-suppress MissingClosureParamType Because of a psalm bug I already
         *                                         filed an issue for
         * @see            https://github.com/vimeo/psalm/issues/4033
         */
        $options[CURLOPT_WRITEFUNCTION] = fn(
            $handle,
            string $chunk
        ): int => $sink->write($chunk);

        // Apply all curl options
        curl_setopt_array($handle, $options);

        // Let curl execute the request
        curl_exec($handle);

        // Rewind the stream, so we get access to it
        $sink->rewind();

        // Retrieve the status code
        $statusCode = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $protocolVersion = (string)curl_getinfo($handle, CURLINFO_HTTP_VERSION);

        // Create a response instance
        $response = $this
            ->getResponseFactory()
            ->createResponse($statusCode)
            ->withProtocolVersion($protocolVersion)
            ->withBody($sink);

        // Set all response headers
        foreach ($responseHeaders as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        // Retrieve any eventual error code
        $errorCode = curl_errno($handle);
        $errorMessage = curl_error($handle) ?: 'none';

        // Close the request
        curl_close($handle);

        switch ($errorCode) {
            // If the curl error code is 0 and the status does not indicate a server
            // error, we're done
            case 0:
                return $response;

            // DNS resolution problems can indicate several specific issues, so
            // they have their own exception class
            case CURLE_COULDNT_RESOLVE_HOST:
                throw new UnresolvableHostException($request->getUri());

            // This group is for all connection errors, happening before we even
            // receive an HTTP response from the server.
            case CURLE_COULDNT_CONNECT:
            case CURLE_TOO_MANY_REDIRECTS:
            case CURLE_GOT_NOTHING:
            case CURLE_FAILED_INIT:
            case CURLE_READ_ERROR:
            case CURLE_RECV_ERROR:
                throw new ConnectionException(
                    $request,
                    $errorMessage,
                    $errorCode
                );

            // This group is for all HTTPS errors relating to SSL certificates.
            case CURLE_SSL_CONNECT_ERROR:
            case CURLE_SSL_CACERT:
            case CURLE_SSL_CACERT_BADFILE:
            case CURLE_SSL_CERTPROBLEM:
            case CURLE_SSL_CIPHER:
            case CURLE_SSL_ENGINE_NOTFOUND:
            case CURLE_SSL_ENGINE_SETFAILED:
            case CURLE_SSL_PINNEDPUBKEYNOTMATCH:
            case CURLE_SSL_PEER_CERTIFICATE:
                throw new SslCertificateException(
                    $request,
                    $errorMessage,
                    $errorCode
                );

            // We were unable to determine the exact error, so we'll throw a generic
            // client error. This should probably not happen.
            default:
                $message = sprintf(
                    "Request failed: Unknown curl error %d: %s",
                    $errorCode,
                    $errorMessage
                );

                throw new ClientException($message, $errorCode);
        }
    }

    protected function getDefaultOptions(): ?array
    {
        return $this->defaultOptions;
    }
}
