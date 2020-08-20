<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Http;

/**
 * HTTP Status Codes based on RFC 7231 and IANA HTTP Status Code Registry.
 *
 * @see     https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
 * @see     https://tools.ietf.org/html/rfc7231
 *
 * @codeCoverageIgnore
 *
 * @package Radiergummi\Wander\Http
 * @author  Moritz Friedrich <m@9dev.de>
 * @license MIT
 */
final class Status
{
    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.3.3
     */
    public const ACCEPTED = 202;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc5842#section-
     */
    public const ALREADY_REPORTED = 208;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.6.3
     */
    public const BAD_GATEWAY = 502;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.1
     */
    public const BAD_REQUEST = 400;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.8
     */
    public const CONFLICT = 409;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.2.1
     */
    public const CONTINUE = 100;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.3.2
     */
    public const CREATED = 201;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc8297#section-
     */
    public const EARLY_HINTS = 103;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.14
     */
    public const EXPECTATION_FAILED = 417;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc4918#section-
     */
    public const FAILED_DEPENDENCY = 424;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.3
     */
    public const FORBIDDEN = 403;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.4.3
     */
    public const FOUND = 302;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.6.5
     */
    public const GATEWAY_TIMEOUT = 504;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.9
     */
    public const GONE = 410;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.6.6
     */
    public const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc2324#section-2.3.2
     */
    public const IM_A_TEAPOT = 418;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc3229#section-
     */
    public const IM_USED = 226;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc4918#section-
     */
    public const INSUFFICIENT_STORAGE = 507;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.6.1
     */
    public const INTERNAL_SERVER_ERROR = 500;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.10
     */
    public const LENGTH_REQUIRED = 411;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc4918#section-
     */
    public const LOCKED = 423;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc5842#section-
     */
    public const LOOP_DETECTED = 508;

    /**
     * Status codes translation table.
     *
     * @var array<int, string>
     */
    public const MESSAGES = [
        self::CONTINUE => 'Continue',
        self::SWITCHING_PROTOCOLS => 'Switching Protocols',
        self::PROCESSING => 'Processing',
        self::EARLY_HINTS => 'Early Hints',
        self::OK => 'OK',
        self::CREATED => 'Created',
        self::ACCEPTED => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        self::NO_CONTENT => 'No Content',
        self::RESET_CONTENT => 'Reset Content',
        self::PARTIAL_CONTENT => 'Partial Content',
        self::MULTI_STATUS => 'Multi-Status',
        self::ALREADY_REPORTED => 'Already Reported',
        self::IM_USED => 'IM Used',
        self::MULTIPLE_CHOICES => 'Multiple Choices',
        self::MOVED_PERMANENTLY => 'Moved Permanently',
        self::FOUND => 'Found',
        self::SEE_OTHER => 'See Other',
        self::NOT_MODIFIED => 'Not Modified',
        self::USE_PROXY => 'Use Proxy',
        self::TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::PERMANENT_REDIRECT => 'Permanent Redirect',
        self::BAD_REQUEST => 'Bad Request',
        self::UNAUTHORIZED => 'Unauthorized',
        self::PAYMENT_REQUIRED => 'Payment Required',
        self::FORBIDDEN => 'Forbidden',
        self::NOT_FOUND => 'Not Found',
        self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::NOT_ACCEPTABLE => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT => 'Request Timeout',
        self::CONFLICT => 'Conflict',
        self::GONE => 'Gone',
        self::LENGTH_REQUIRED => 'Length Required',
        self::PAYLOAD_TOO_LARGE => 'Payload Too Large',
        self::URI_TOO_LONG => 'URI Too Long',
        self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::RANGE_NOT_SATISFIABLE => 'Range Not Satisfiable',
        self::EXPECTATION_FAILED => 'Expectation Failed',
        self::IM_A_TEAPOT => 'I\'m a teapot',
        self::MISDIRECTED_REQUEST => 'Misdirected Request',
        self::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        self::LOCKED => 'Locked',
        self::FAILED_DEPENDENCY => 'Failed Dependency',
        self::TOO_EARLY => 'Too Early',
        self::UPGRADE_REQUIRED => 'Upgrade Required',
        self::PRECONDITION_FAILED => 'Precondition Required',
        self::TOO_MANY_REQUESTS => 'Too Many Requests',
        self::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        self::UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
        self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::NOT_IMPLEMENTED => 'Not Implemented',
        self::BAD_GATEWAY => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::GATEWAY_TIMEOUT => 'Gateway Timeout',
        self::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        self::VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        self::INSUFFICIENT_STORAGE => 'Insufficient Storage',
        self::LOOP_DETECTED => 'Loop Detected',
        self::NOT_EXTENDED => 'Not Extended',
        self::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
    ];

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.5
     */
    public const METHOD_NOT_ALLOWED = 405;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7540#section-9.1.2
     */
    public const MISDIRECTED_REQUEST = 421;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.4.2
     */
    public const MOVED_PERMANENTLY = 301;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.4.1
     */
    public const MULTIPLE_CHOICES = 300;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc4918#section-
     */
    public const MULTI_STATUS = 207;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc6585#section-
     */
    public const NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.3.4
     */
    public const NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.6
     */
    public const NOT_ACCEPTABLE = 406;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc2774#section-
     */
    public const NOT_EXTENDED = 510;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.4
     */
    public const NOT_FOUND = 404;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.6.2
     */
    public const NOT_IMPLEMENTED = 501;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7232#section-4.1
     */
    public const NOT_MODIFIED = 304;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.3.5
     */
    public const NO_CONTENT = 204;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.3.1
     */
    public const OK = 200;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7233#section-4.1
     */
    public const PARTIAL_CONTENT = 206;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.11
     */
    public const PAYLOAD_TOO_LARGE = 413;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.2
     */
    public const PAYMENT_REQUIRED = 402;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7538#section-
     */
    public const PERMANENT_REDIRECT = 308;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7232#section-4.2
     */
    public const PRECONDITION_FAILED = 412;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc6585#section-
     */
    public const PRECONDITION_REQUIRED = 428;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc2518#section-
     */
    public const PROCESSING = 102;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7235#section-3.2
     */
    public const PROXY_AUTHENTICATION_REQUIRED = 407;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7233#section-4.4
     */
    public const RANGE_NOT_SATISFIABLE = 416;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc6585#section-
     */
    public const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.7
     */
    public const REQUEST_TIMEOUT = 408;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.3.6
     */
    public const RESET_CONTENT = 205;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.4.4
     */
    public const SEE_OTHER = 303;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.6.4
     */
    public const SERVICE_UNAVAILABLE = 503;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.2.2
     */
    public const SWITCHING_PROTOCOLS = 101;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.4.7
     */
    public const TEMPORARY_REDIRECT = 307;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc8470#section-
     */
    public const TOO_EARLY = 425;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc6585#section-
     */
    public const TOO_MANY_REQUESTS = 429;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7235#section-3.1
     */
    public const UNAUTHORIZED = 401;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7725#section-
     */
    public const UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc4918#section-
     */
    public const UNPROCESSABLE_ENTITY = 422;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.13
     */
    public const UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.15
     */
    public const UPGRADE_REQUIRED = 426;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.5.12
     */
    public const URI_TOO_LONG = 414;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc7231#section-6.4.5
     */
    public const USE_PROXY = 305;

    /**
     * @var int
     * @see https://tools.ietf.org/html/rfc2295#section-
     */
    public const VARIANT_ALSO_NEGOTIATES = 506;

    private function __construct()
    {
        // No-Op
    }

    public static function getErrorCodes(): array
    {
        return [
            self::BAD_REQUEST => 'Bad Request',
            self::UNAUTHORIZED => 'Unauthorized',
            self::PAYMENT_REQUIRED => 'Payment Required',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Not Found',
            self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
            self::NOT_ACCEPTABLE => 'Not Acceptable',
            self::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
            self::REQUEST_TIMEOUT => 'Request Timeout',
            self::CONFLICT => 'Conflict',
            self::GONE => 'Gone',
            self::LENGTH_REQUIRED => 'Length Required',
            self::PAYLOAD_TOO_LARGE => 'Payload Too Large',
            self::URI_TOO_LONG => 'URI Too Long',
            self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
            self::RANGE_NOT_SATISFIABLE => 'Range Not Satisfiable',
            self::EXPECTATION_FAILED => 'Expectation Failed',
            self::IM_A_TEAPOT => 'I\'m a teapot',
            self::MISDIRECTED_REQUEST => 'Misdirected Request',
            self::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
            self::LOCKED => 'Locked',
            self::FAILED_DEPENDENCY => 'Failed Dependency',
            self::TOO_EARLY => 'Too Early',
            self::UPGRADE_REQUIRED => 'Upgrade Required',
            self::PRECONDITION_FAILED => 'Precondition Required',
            self::TOO_MANY_REQUESTS => 'Too Many Requests',
            self::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
            self::UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
            self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
            self::NOT_IMPLEMENTED => 'Not Implemented',
            self::BAD_GATEWAY => 'Bad Gateway',
            self::SERVICE_UNAVAILABLE => 'Service Unavailable',
            self::GATEWAY_TIMEOUT => 'Gateway Timeout',
            self::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
            self::VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
            self::INSUFFICIENT_STORAGE => 'Insufficient Storage',
            self::LOOP_DETECTED => 'Loop Detected',
            self::NOT_EXTENDED => 'Not Extended',
            self::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
        ];
    }

    /**
     * Checks whether a status code is a valid status code
     *
     * @param int $code
     *
     * @return bool
     */
    public static function isValid(int $code): bool
    {
        return (
            ($code > 99 && $code < 104) ||
            ($code > 199 && $code < 209) ||
            ($code === 226) ||
            ($code > 299 && $code < 309) ||
            ($code > 399 && $code < 418) ||
            ($code > 420 && $code < 432) ||
            ($code === 451) ||
            ($code > 499 && $code < 512) ||
            ($code > 499 && $code < 512)
        );
    }

    /**
     * Whether the status code is an informational code
     *
     * @param int $statusCode
     *
     * @return bool
     */
    public static function isInformational(int $statusCode): bool
    {
        return $statusCode > 99 && $statusCode < 200;
    }

    /**
     * Whether the status code is a success code
     *
     * @param int $statusCode
     *
     * @return bool
     */
    public static function isSuccess(int $statusCode): bool
    {
        return $statusCode > 199 && $statusCode < 300;
    }

    /**
     * Whether the status code is a redirection code
     *
     * @param int $statusCode
     *
     * @return bool
     */
    public static function isRedirection(int $statusCode): bool
    {
        return $statusCode > 299 && $statusCode < 400;
    }

    /**
     * Whether the status code is an error code
     *
     * @param int $statusCode
     *
     * @return bool
     */
    public static function isError(int $statusCode): bool
    {
        return $statusCode > 399 && $statusCode < 600;
    }

    /**
     * Whether the status code is a client error code
     *
     * @param int $statusCode
     *
     * @return bool
     */
    public static function isClientError(int $statusCode): bool
    {
        return $statusCode > 399 && $statusCode < 500;
    }

    /**
     * Whether the status code is a server error code
     *
     * @param int $statusCode
     *
     * @return bool
     */
    public static function isServerError(int $statusCode): bool
    {
        return $statusCode > 499 && $statusCode < 600;
    }

    /**
     * Retrieves the message for a status code
     *
     * @param int $statusCode
     *
     * @return string|null
     */
    public static function getMessage(int $statusCode): ?string
    {
        return self::MESSAGES[$statusCode] ?? null;
    }
}
