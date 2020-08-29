<?php

declare(strict_types=1);

namespace Radiergummi\Wander;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Context\RequestContext;
use Radiergummi\Wander\Drivers\StreamDriver;
use Radiergummi\Wander\Exceptions\ResponseErrorException;
use Radiergummi\Wander\Http\MediaType as Type;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Http\Status;
use Radiergummi\Wander\Interfaces\DriverInterface;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use Radiergummi\Wander\Interfaces\SerializerInterface;
use Radiergummi\Wander\Interfaces\SerializerRegistryInterface;
use Radiergummi\Wander\Serializers\JsonSerializer;
use Radiergummi\Wander\Serializers\PlainTextSerializer;
use Radiergummi\Wander\Serializers\UrlEncodedSerializer;

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
    public const USER_AGENT = 'wander/1.0.0';

    protected DriverInterface $driver;

    protected RequestFactoryInterface $requestFactory;

    protected ResponseFactoryInterface $responseFactory;

    protected SerializerRegistryInterface $serializerRegistry;

    public function __construct(
        ?DriverInterface $driver = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null
    ) {
        $factory = new Psr17Factory();

        $this->setRequestFactory($requestFactory ?? $factory);
        $this->setResponseFactory($responseFactory ?? $factory);
        $this->setDriver($driver ?? new StreamDriver());

        $this->serializerRegistry = new SerializerRegistry();
        $this->serializerRegistry->register(
            Type::APPLICATION_JSON,
            new JsonSerializer()
        );
        $this->serializerRegistry->register(
            Type::TEXT_PLAIN,
            new PlainTextSerializer()
        );
        $this->serializerRegistry->register(
            Type::APPLICATION_X_WWW_FORM_URLENCODED,
            new UrlEncodedSerializer()
        );
    }

    public function setDriver(DriverInterface $driver): void
    {
        $this->driver = $driver;
        $this->driver->setResponseFactory(
            $this->responseFactory
        );
    }

    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    public function setRequestFactory(
        RequestFactoryInterface $requestFactory
    ): void {
        $this->requestFactory = $requestFactory;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    public function setResponseFactory(
        ResponseFactoryInterface $responseFactory
    ): void {
        $this->responseFactory = $responseFactory;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    /**
     * @inheritDoc
     */
    public function getSerializerRegistry(): SerializerRegistryInterface
    {
        return $this->serializerRegistry;
    }

    /**
     * Adds a new body serializer
     *
     * @param string              $mediaType
     * @param SerializerInterface $serializer
     *
     * @return $this
     */
    public function addSerializer(
        string $mediaType,
        SerializerInterface $serializer
    ): self {
        $this->serializerRegistry->register(
            $mediaType,
            $serializer
        );

        return $this;
    }

    /**
     * Shorthand to create a GET request
     *
     * @param UriInterface|string $uri
     *
     * @return RequestContext
     */
    public function get($uri): RequestContext
    {
        return $this->createContext(Method::GET, $uri);
    }

    /**
     * Shorthand to create a HEAD request
     *
     * @param UriInterface|string $uri
     *
     * @return RequestContext
     */
    public function head($uri): RequestContext
    {
        return $this->createContext(Method::HEAD, $uri);
    }

    /**
     * Shorthand to create a DELETE request
     *
     * @param UriInterface|string $uri
     *
     * @return RequestContext
     */
    public function options($uri): RequestContext
    {
        return $this->createContext(Method::OPTIONS, $uri);
    }

    /**
     * Shorthand to create a DELETE request
     *
     * @param UriInterface|string $uri
     *
     * @return RequestContext
     */
    public function delete($uri): RequestContext
    {
        return $this->createContext(Method::DELETE, $uri);
    }

    /**
     * Shorthand to create a POST request
     *
     * @param UriInterface|string $uri
     * @param mixed|null          $body
     *
     * @return RequestContext
     */
    public function post($uri, $body = null): RequestContext
    {
        $context = $this->createContext(Method::POST, $uri);

        if ($body) {
            $context->withBody($body);
        }

        return $context;
    }

    /**
     * Shorthand to create a PUT request
     *
     * @param UriInterface|string $uri
     * @param mixed|null          $body
     *
     * @return RequestContext
     */
    public function put($uri, $body = null): RequestContext
    {
        $context = $this->createContext(Method::PUT, $uri);

        if ($body) {
            $context->withBody($body);
        }

        return $context;
    }

    /**
     * Shorthand to create a PATCH request
     *
     * @param UriInterface|string $uri
     * @param mixed|null          $body
     *
     * @return RequestContext
     */
    public function patch($uri, $body = null): RequestContext
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
     * @return RequestContext
     */
    public function createContext(string $method, $uri): RequestContext
    {
        $request = $this->requestFactory->createRequest(
            $method,
            $uri
        );

        return $this->createContextFromRequest($request);
    }

    /**
     * Creates a new request context from an existing request instance
     *
     * @param RequestInterface $request Request instance to create a context for
     *
     * @return RequestContext
     */
    public function createContextFromRequest(
        RequestInterface $request
    ): RequestContext {
        return new RequestContext($this, $request);
    }

    /**
     * @inheritDoc
     */
    public function request(RequestInterface $request): ResponseInterface
    {
        $response = $this->driver->sendRequest($request);

        if (Status::isError($response->getStatusCode())) {
            throw ResponseErrorException::create(
                $request,
                $response
            );
        }

        return $response;
    }
}
