<?php

namespace Radiergummi\Wander\Exceptions;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class ConnectionException extends WanderException implements NetworkExceptionInterface
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
            "Unable to connect to remote server: {$message}",
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
