<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Serializers;

use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Radiergummi\Wander\Interfaces\SerializerInterface;
use RuntimeException;

class PlainTextSerializer implements SerializerInterface
{
    /**
     * Writes URL encoded data to the request body
     *
     * @param RequestInterface $request
     * @param mixed            $body
     *
     * @return RequestInterface
     * @throws InvalidArgumentException
     */
    public function apply(
        RequestInterface $request,
        $body
    ): RequestInterface {
        $encoded = (string)$body;
        $stream = Stream::create($encoded);

        return $request->withBody($stream);
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function extract(ResponseInterface $response)
    {
        return $response->getBody()->getContents();
    }
}
