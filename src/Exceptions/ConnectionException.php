<?php

namespace Radiergummi\Wander\Exceptions;

use Throwable;

class ConnectionException extends WanderException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            "Unable to connect to remote server: {$message}",
            $code,
            $previous
        );
    }
}
