<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Interfaces;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface SerializerInterface
{
    /**
     * Applies the given body data on the given request
     *
     * @param RequestInterface $request
     * @param mixed            $body
     *
     * @return RequestInterface
     */
    public function apply(
        RequestInterface $request,
        $body
    ): RequestInterface;

    /**
     * Extracts a body from the given response
     *
     * @param ResponseInterface $response
     *
     * @return mixed
     */
    public function extract(ResponseInterface $response);
}
