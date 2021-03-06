<?php
/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Radiergummi\Wander\Drivers;

use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Radiergummi\Wander\Drivers\Features\RedirectsTrait;
use Radiergummi\Wander\Drivers\Features\TimeoutTrait;
use Radiergummi\Wander\Exceptions\ClientException;
use Radiergummi\Wander\Exceptions\ConnectionException;
use Radiergummi\Wander\Exceptions\DriverException;
use Radiergummi\Wander\Exceptions\SslCertificateException;
use Radiergummi\Wander\Exceptions\UnresolvableHostException;
use Radiergummi\Wander\Http\Header;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Http\Status;
use Radiergummi\Wander\Interfaces\Features\SupportsRedirectsInterface;
use Radiergummi\Wander\Interfaces\Features\SupportsTimeoutsInterface;
use RuntimeException;
use UnexpectedValueException;

use function constant;
use function count;
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function defined;
use function explode;
use function filter_var;
use function sprintf;
use function strlen;
use function strtolower;
use function trim;

use const CURL_HTTP_VERSION_1_0;
use const CURL_HTTP_VERSION_1_1;
use const CURL_HTTP_VERSION_2_0;
use const CURL_HTTP_VERSION_NONE;
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
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_FAILONERROR;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_HEADERFUNCTION;
use const CURLOPT_HTTP200ALIASES;
use const CURLOPT_HTTP_VERSION;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_INFILE;
use const CURLOPT_INFILESIZE;
use const CURLOPT_MAXREDIRS;
use const CURLOPT_POST;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT_MS;
use const CURLOPT_UPLOAD;
use const CURLOPT_URL;
use const CURLOPT_WRITEFUNCTION;
use const FILTER_VALIDATE_URL;

/**
 * Curl Driver
 * ===========
 * A driver implementation that uses curl to make requests
 *
 * @package Radiergummi\Wander\Drivers
 * @author  Moritz Friedrich <m@9dev.de>
 * @license MIT
 */
class CurlDriver extends AbstractDriver implements SupportsTimeoutsInterface,
                                                   SupportsRedirectsInterface
{
    use TimeoutTrait;
    use RedirectsTrait;

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
     * @noinspection CallableParameterUseCaseInTypeContextInspection
     * @noinspection PhpUnusedParameterInspection
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $url = (string)$request->geturi();

        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new DriverException(
                $request,
                "URL '{$url}' is not a valid URL"
            );
        }

        // Create a curl handle
        $handle = curl_init();
        $options = $this->getDefaultOptions() ?? [];

        // Set the URL
        $options[CURLOPT_URL] = $url;

        // Set the HTTP version to use
        $options[CURLOPT_HTTP_VERSION] = $this->resolveProtocolVersion(
            $request->getProtocolVersion()
        );

        // Alias *all* HTTP error codes as 200 and work them out on the client
        $options[CURLOPT_HTTP200ALIASES] = Status::getErrorCodes();
        $options[CURLOPT_FAILONERROR] = false;

        // If we've got a body, append it to the request
        if (
            ($bodyLength = $request->getBody()->getSize()) > 0 &&
            ! Method::mayNotIncludeBody($request->getMethod())
        ) {
            $options[CURLOPT_INFILE] = static::detach(
                $request->getBody()
            );
            $options[CURLOPT_INFILESIZE] = $bodyLength;

            // CURLOPT_UPLOAD makes the request a streaming upload - and sets
            // the request method to PUT as an unwanted side effect, so we'll
            // need to set it below
            $options[CURLOPT_UPLOAD] = true;

            // Set the Content-Length header, unless already configured
            if ( ! $request->hasHeader(Header::CONTENT_LENGTH)) {
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
             * requests. The way its implemented, though, is incompatible with
             * most web servers: It sends an `Expect: 100 Continue` header
             * before actually submitting the request. To prevent this problem,
             * PUT has been added to the custom request block below, which works
             * just fine.
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

        // TODO: Should this really override a curl option configured using the
        //       default curl options?
        if ($this->timeout) {
            $options[CURLOPT_TIMEOUT_MS] = $this->timeout;
        }

        if ($this->followRedirects) {
            $options[CURLOPT_FOLLOWLOCATION] = $this->followRedirects;

            // Apply the maximum redirects limit
            if ($this->maximumRedirects !== null) {
                $options[CURLOPT_MAXREDIRS] = $this->maximumRedirects;
            }
        }

        // Set all request headers
        $options[CURLOPT_HTTPHEADER] = self::marshalHeaders(
            $request->getHeaders()
        );

        // Make sure curl keeps the response body
        $options[CURLOPT_RETURNTRANSFER] = false;

        $responseHeaders = [];

        $options[CURLOPT_HEADERFUNCTION] =

            /**
             * The header function is run by curl to process the
             * response headers.
             *
             * @param resource $handle
             * @param string   $header
             *
             * @return int
             * @see https://github.com/vimeo/psalm/issues/4033
             */
            static function (
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

                if ( ! isset($responseHeaders[$name])) {
                    $responseHeaders[$name] = [];
                }

                $responseHeaders[$name][] = trim($parts[1]);

                return $length;
            };

        $sink = Stream::create();

        $options[CURLOPT_WRITEFUNCTION] =
            /**
             * @param resource $handle
             * @param string   $chunk
             *
             * @return int
             * @see https://github.com/vimeo/psalm/issues/4033
             */
            fn($handle, string $chunk): int => $sink->write($chunk);

        // Apply all curl options
        curl_setopt_array($handle, $options);

        // Let curl execute the request
        curl_exec($handle);

        // Rewind the stream, so we get access to it
        $sink->rewind();

        // Retrieve the status code
        $statusCode = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);

        // Retrieve the protocol version if possible. This constant was added
        // only in curl 7.5.0, which is fairly new even for this library
        $protocolVersion = defined('CURLINFO_HTTP_VERSION')
            ? (string)curl_getinfo(
                $handle,
                (int)constant('CURLINFO_HTTP_VERSION')
            )
            : '1.1';

        // Create a response instance
        $response = $this
            ->getResponseFactory()
            ->createResponse($statusCode)
            ->withProtocolVersion($protocolVersion)
            ->withBody($sink);

        // Set all response headers
        /** @var array<string,string[]> $responseHeaders */
        foreach ($responseHeaders as $name => $values) {
            $response = $response->withHeader($name, $values);
        }

        // Retrieve any eventual error code
        $errorCode = curl_errno($handle);
        $errorMessage = curl_error($handle) ?: 'none';

        // Close the request
        curl_close($handle);

        switch ($errorCode) {
            // If the curl error code is 0 and the status does not indicate a
            // request error, we're done
            case 0:
                return $response;

            // DNS resolution problems can indicate several specific issues, so
            // they have their own exception class
            case CURLE_COULDNT_RESOLVE_HOST:
                throw new UnresolvableHostException($request);

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

            // We were unable to determine the exact error, so we'll throw a
            // generic client error. This should probably not happen.
            default:
                $message = sprintf(
                    "Request failed: Unknown curl error %d: %s",
                    $errorCode,
                    $errorMessage
                );

                throw new ClientException($message, $errorCode);
        }
    }

    /**
     * Retrieves the default curl options as configured by the user.
     *
     * @return array|null
     */
    public function getDefaultOptions(): ?array
    {
        return $this->defaultOptions;
    }

    /**
     * Resolves the HTTP protocol version to a curl constant.
     *
     * @param string $protocolVersion
     *
     * @return int
     * @throws UnexpectedValueException
     */
    final protected function resolveProtocolVersion(string $protocolVersion): int
    {
        switch ($protocolVersion) {
            case '1.0':
                return CURL_HTTP_VERSION_1_0;
            case '1.1':
                return CURL_HTTP_VERSION_1_1;
            case '2.0':
                if ( ! defined('CURL_HTTP_VERSION_2_0')) {
                    throw new UnexpectedValueException(
                        'Installed libcurl version has no HTTP 2.0 ' .
                        'support. Update to libcurl 7.33 or greater to send ' .
                        'HTTP 2.0 requests.'
                    );
                }

                return CURL_HTTP_VERSION_2_0;
        }

        return CURL_HTTP_VERSION_NONE;
    }
}
