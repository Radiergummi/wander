<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Serializers;

use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Radiergummi\Wander\Interfaces\SerializerInterface;

use function http_build_query;

class UrlEncodedSerializer implements SerializerInterface
{
    /**
     * Writes URL encoded data to the request body
     *
     * @param RequestInterface $request
     * @param mixed            $body
     *
     * @return RequestInterface
     */
    public function applyBody(RequestInterface $request, $body): RequestInterface
    {
        if (!is_array($body) && !is_object($body)) {
            throw new InvalidArgumentException('Only arrays or objects may be URL encoded');
        }

        $encoded = http_build_query($body);
        $stream = Stream::create($encoded);

        return $request->withBody($stream);
    }
}
