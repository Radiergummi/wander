<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Serializers;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Radiergummi\Wander\Interfaces\SerializerInterface;

class PlainTextSerializer implements SerializerInterface
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
        $encoded = (string)$body;
        $stream = Stream::create($encoded);

        return $request->withBody($stream);
    }
}
