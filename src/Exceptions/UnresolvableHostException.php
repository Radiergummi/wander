<?php

namespace Radiergummi\Wander\Exceptions;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

class UnresolvableHostException extends DriverException
{
    public function __construct(
        RequestInterface $request,
        Throwable $previous = null
    ) {
        $hostname = $request->getUri()->getHost();

        parent::__construct(
            $request,
            "Could not resolve host '{$hostname}'",
            0,
            $previous
        );
    }
}
