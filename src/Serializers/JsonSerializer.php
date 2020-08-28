<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Serializers;

use InvalidArgumentException;
use JsonException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Radiergummi\Wander\Interfaces\SerializerInterface;

use function json_encode;

use const JSON_THROW_ON_ERROR;

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
     * @throws InvalidArgumentException
     */
    public function applyBody(
        RequestInterface $request,
        $body
    ): RequestInterface {
        $encoded = json_encode($body, JSON_THROW_ON_ERROR);
        $stream = Stream::create($encoded);

        return $request->withBody($stream);
    }
}
