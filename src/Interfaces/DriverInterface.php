<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Interfaces;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

interface DriverInterface extends ClientInterface
{
    /**
     * Sets the response factory instance to use.
     *
     * @param ResponseFactoryInterface $responseFactory Instance of a PSR-17 factory.
     */
    public function setResponseFactory(
        ResponseFactoryInterface $responseFactory
    ): void;

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
