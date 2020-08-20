<?php

declare(strict_types=1);

namespace Radiergummi\Wander;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Drivers\StreamDriver;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Interfaces\DriverInterface;
use Radiergummi\Wander\Interfaces\HttpClientInterface;

/**
 * Wander Client
 * =============
 * The HTTP clients instance
 *
 * @package Radiergummi\Wander
 * @author  Moritz Friedrich <m@9dev.de>
 * @license MIT
 */
class Wander implements HttpClientInterface
{
    protected DriverInterface $driver;

    protected RequestFactoryInterface $requestFactory;

    protected ?ResponseFactoryInterface $responseFactory;

    public function __construct(
        ?DriverInterface $driver = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null
    ) {
        $psr17Factory = new Psr17Factory();
        $this->requestFactory = $requestFactory ?? $psr17Factory;
        $this->responseFactory = $responseFactory ?? $psr17Factory;

        $this->driver = $driver ?? new StreamDriver();
        $this->driver->setResponseFactory($this->responseFactory);
    }

    public function get($uri): Context
    {
        return $this->createContext(Method::GET, $uri);
    }

    public function head($uri): Context
    {
        return $this->createContext(Method::HEAD, $uri);
    }

    public function options($uri): Context
    {
        return $this->createContext(Method::OPTIONS, $uri);
    }

    public function delete($uri): Context
    {
        return $this->createContext(Method::DELETE, $uri);
    }

    public function post($uri, $body = null): Context
    {
        $context = $this->createContext(Method::POST, $uri);

        if ($body) {
            $context->withBody($body);
        }

        return $context;
    }

    public function put($uri, $body = null): Context
    {
        $context = $this->createContext(Method::PUT, $uri);

        if ($body) {
            $context->withBody($body);
        }

        return $context;
    }

    public function patch($uri, $body = null): Context
    {
        $context = $this->createContext(Method::PATCH, $uri);

        if ($body) {
            $context->withBody($body);
        }

        return $context;
    }

    /**
     * Creates a new request and wraps it in a context
     *
     * @param string              $method HTTP request method.
     * @param string|UriInterface $uri    Request URI as string or URI instance.
     *
     * @return Context
     */
    public function createContext(string $method, $uri): Context
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        return $this->createContextFromRequest($request);
    }

    /**
     * Creates a new request context from an existing request instance
     *
     * @param RequestInterface $request Request instance to create a context for
     *
     * @return Context
     */
    public function createContextFromRequest(RequestInterface $request): Context
    {
        return new Context($this, $request);
    }

    /**
     * @inheritDoc
     */
    public function request(RequestInterface $request): ResponseInterface
    {
        return $this->driver->sendRequest($request);
    }
}
