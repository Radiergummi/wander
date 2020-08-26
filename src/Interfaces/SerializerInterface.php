<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Interfaces;

use Psr\Http\Message\RequestInterface;

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
    public function applyBody(
        RequestInterface $request,
        $body
    ): RequestInterface;
}
