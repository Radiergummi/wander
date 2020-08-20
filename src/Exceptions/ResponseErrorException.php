<?php

namespace Radiergummi\Wander\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseErrorException extends WanderException
{
    protected RequestInterface $request;

    protected ResponseInterface $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->request = $request;
        $this->response = $response;

        parent::__construct(
            "Request failed with status {$response->getStatusCode()}",
            $response->getStatusCode()
        );
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
