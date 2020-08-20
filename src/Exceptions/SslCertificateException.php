<?php

namespace Radiergummi\Wander\Exceptions;

use Throwable;

class SslCertificateException extends WanderException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            "Failed to establish secure connection: {$message}",
            $code,
            $previous
        );
    }
}
