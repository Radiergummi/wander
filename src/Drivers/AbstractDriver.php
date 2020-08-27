<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Drivers;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Radiergummi\Wander\Interfaces\DriverInterface;
use RuntimeException;

abstract class AbstractDriver implements DriverInterface
{
    private ResponseFactoryInterface $responseFactory;

    /**
     * Transforms request headers into a format usable by curl
     *
     * @param string[][] $headers
     *
     * @return string[]
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
     * @throws RuntimeException
     */
    protected static function detach(StreamInterface $stream)
    {
        $clone = clone $stream;
        $clone->rewind();

        return $clone->detach();
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
