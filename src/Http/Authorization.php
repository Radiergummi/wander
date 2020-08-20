<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Http;

/**
 * Authentication Schemes based on IANA Authentication Scheme Registry.
 *
 * @see     https://www.iana.org/assignments/http-authschemes/http-authschemes.xhtml
 * @see     https://tools.ietf.org/html/rfc7235
 *
 * @codeCoverageIgnore
 *
 * @package Radiergummi\Wander\Http
 * @author  Moritz Friedrich <m@9dev.de>
 * @license MIT
 */
final class Authorization
{
    /**
     * @see https://docs.aws.amazon.com/AmazonS3/latest/API/sigv4-auth-using-authorization-header.html
     */
    public const AWS4_HMAC_SHA256 = 'AWS4-HMAC-SHA256';

    /**
     * @see https://tools.ietf.org/html/rfc7617
     */
    public const BASIC = 'Basic';

    /**
     * @see https://tools.ietf.org/html/rfc6750
     */
    public const BEARER = 'Bearer';

    /**
     * @see https://tools.ietf.org/html/rfc7616
     */
    public const DIGEST = 'Digest';

    /**
     * IANA Note: The HOBA scheme can be used with either HTTP servers or proxies.
     * When used in response to a 407 Proxy Authentication Required indication, the
     * appropriate proxy authentication header fields are used instead, as with any
     * other HTTP authentication scheme.
     *
     * @see https://www.iana.org/go/rfc7486
     */
    public const HOBA = 'HOBA';

    /**
     * @see https://tools.ietf.org/html/rfc8120
     */
    public const MUTUAL = 'Mutual';

    /**
     * IANA Note: This authentication scheme violates both HTTP semantics (being
     * connection-oriented) and syntax (use of syntax incompatible with the
     * `WWW-Authenticate` and `Authorization` header field syntax).
     *
     * @see https://www.iana.org/go/rfc4559
     */
    public const NEGOTIATE = 'Negotiate';

    /**
     * @see https://www.iana.org/go/rfc5849
     */
    public const OAUTH = 'OAuth';

    /**
     * @see https://www.iana.org/go/rfc7804
     */
    public const SCRAM_SHA_1 = 'SCRAM-SHA-1';

    /**
     * @see https://www.iana.org/go/rfc7804
     */
    public const SCRAM_SHA_256 = 'SCRAM-SHA-256';

    /**
     * @see https://www.iana.org/go/rfc8292
     */
    public const VAPID = 'vapid';
}
