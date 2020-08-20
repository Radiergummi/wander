<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Drivers;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Radiergummi\Wander\Interfaces\DriverInterface;

abstract class AbstractDriver implements DriverInterface
{
    private ResponseFactoryInterface $responseFactory;

    /**
     * Transforms request headers into a format usable by curl
     *
     * @param array $headers
     *
     * @return array
     */
    protected static function marshalHeaders(array $headers): array
    {
        $headerLines = [];

        foreach ($headers as $header => $values) {
            foreach ($values as $value) {
                $headerLines[] = "{$header}: {$value}";
            }
        }

        return $headerLines;
    }

    /**
     * Retrieves the raw, detached stream from a stream interface
     *
     * @param StreamInterface $stream
     *
     * @return resource|null
     */
    protected static function detach(StreamInterface $stream)
    {
        $stream = clone $stream;

        return $stream->detach();
    }

    /**
     * @inheritDoc
     */
    final public function setResponseFactory(
        ResponseFactoryInterface $responseFactory
    ): void {
        $this->responseFactory = $responseFactory;
    }

    final protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }
}
