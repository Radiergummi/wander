<?php

namespace Radiergummi\Wander\Exceptions;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class SslCertificateException extends WanderException implements RequestExceptionInterface
{
    protected RequestInterface $request;

    public function __construct(
        RequestInterface $request,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->request = $request;

        parent::__construct(
            "Failed to establish secure connection: {$message}",
            $code,
            $previous
        );
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
