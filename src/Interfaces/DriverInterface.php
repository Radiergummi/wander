<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Interfaces;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;

interface DriverInterface extends ClientInterface
{
    /**
     * Sets the response factory instance to use.
     *
     * @param ResponseFactoryInterface $responseFactory Instance of a PSR-17 factory.
     * @throws DriverException If an error occurs before dispatching the request
     */
    public function setResponseFactory(
        ResponseFactoryInterface $responseFactory
    ): void;
}
