<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Http;

use function in_array;

/**
 * HTTP Methods based on RFC 7231 and the IANA HTTP Method Registry.
 *
 * @see     https://tools.ietf.org/html/rfc7231#section-4.1
 * @see     http://www.iana.org/assignments/http-methods/http-methods.xhtml
 *
 * @codeCoverageIgnore
 *
 * @package Radiergummi\Wander\Http
 * @author  Moritz Friedrich <m@9dev.de>
 * @license MIT
 */
final class Method
{
    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3744#section-8.1
     */
    public const ACL = 'ACL';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-12.6
     */
    public const BASELINE_CONTROL = 'BASELINE-CONTROL';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc5842#section-4
     */
    public const BIND = 'BIND';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-4.4
     */
    public const CHECKIN = 'CHECKIN';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-4.3
     */
    public const CHECKOUT = 'CHECKOUT';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.6
     */
    public const CONNECT = 'CONNECT';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4918#section-9.8
     */
    public const COPY = 'COPY';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.5
     */
    public const DELETE = 'DELETE';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.1
     */
    public const GET = 'GET';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.2
     */
    public const HEAD = 'HEAD';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-8.2
     */
    public const LABEL = 'LABEL';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc2068#section-19.6.1.2
     */
    public const LINK = 'LINK';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4918#section-9.10
     */
    public const LOCK = 'LOCK';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-11.2
     */
    public const MERGE = 'MERGE';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-13.5
     */
    public const MKACTIVITY = 'MKACTIVITY';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4791#section-5.3.1
     */
    public const MKCALENDAR = 'MKCALENDAR';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4918#section-9.3
     */
    public const MKCOL = 'MKCOL';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4437#section-6
     */
    public const MKREDIRECTREF = 'MKREDIRECTREF';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-6.3
     */
    public const MKWORKSPACE = 'MKWORKSPACE';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4918#section-9.9
     */
    public const MOVE = 'MOVE';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.7
     */
    public const OPTIONS = 'OPTIONS';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3648#section-7
     */
    public const ORDERPATCH = 'ORDERPATCH';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc5789
     */
    public const PATCH = 'PATCH';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.3
     */
    public const POST = 'POST';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc7540#section-3.5
     */
    public const PRI = 'PRI';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4918#section-9.1
     */
    public const PROPFIND = 'PROPFIND';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4918#section-9.2
     */
    public const PROPPATCH = 'PROPPATCH';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.4
     */
    public const PUT = 'PUT';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc5842#section-6
     */
    public const REBIND = 'REBIND';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-3.6
     */
    public const REPORT = 'REPORT';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc5323#section-2
     */
    public const SEARCH = 'SEARCH';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.8
     */
    public const TRACE = 'TRACE';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc5842#section-5
     */
    public const UNBIND = 'UNBIND';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-4.5
     */
    public const UNCHECKOUT = 'UNCHECKOUT';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc2068#section-19.6.1.3
     */
    public const UNLINK = 'UNLINK';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4918#section-9.11
     */
    public const UNLOCK = 'UNLOCK';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-7.1
     */
    public const UPDATE = 'UPDATE';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc4437#section-7
     */
    public const UPDATEREDIRECTREF = 'UPDATEREDIRECTREF';

    /**
     * @var string
     * @see https://tools.ietf.org/html/rfc3253#section-3.5
     */
    public const VERSION_CONTROL = 'VERSION-CONTROL';

    /**
     * Whether a given method may explicitly NOT contain a body
     *
     * @param string $method
     *
     * @return bool
     */
    public static function mayNotIncludeBody(string $method): bool
    {
        return in_array(
            $method,
            [self::GET, self::HEAD],
            true
        );
    }

    private function __construct()
    {
        // No-Op
    }
}
