<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Exceptions;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class DriverException extends WanderException implements RequestExceptionInterface
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
            "Failed to create request: {$message}",
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
