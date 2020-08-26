<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Serializers;

use JsonException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Radiergummi\Wander\Interfaces\SerializerInterface;

use function json_encode;

class JsonSerializer implements SerializerInterface
{
    /**
     * Writes JSON data to the request body
     *
     * @param RequestInterface $request
     * @param mixed            $body
     *
     * @return RequestInterface
     * @throws JsonException
     */
    public function applyBody(RequestInterface $request, $body): RequestInterface
    {
        $encoded = json_encode($body, JSON_THROW_ON_ERROR);
        $stream = Stream::create($encoded);

        return $request->withBody($stream);
    }
}
