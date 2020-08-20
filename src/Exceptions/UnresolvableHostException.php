<?php

namespace Radiergummi\Wander\Exceptions;

use Psr\Http\Message\UriInterface;
use Throwable;

class UnresolvableHostException extends WanderException
{
    public function __construct(
        UriInterface $uri,
        Throwable $previous = null
    ) {
        $hostname = $uri->getHost();

        parent::__construct(
            "Could not resolve host {$hostname}",
            0,
            $previous
        );
    }
}
