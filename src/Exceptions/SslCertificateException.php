<?php

namespace Radiergummi\Wander\Exceptions;

use Psr\Http\Message\RequestInterface;
use Throwable;

class SslCertificateException extends ConnectionException
{
    public function __construct(
        RequestInterface $request,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->request = $request;

        parent::__construct(
            $request,
            "Failed to establish secure connection: {$message}",
            $code,
            $previous
        );
    }
}
