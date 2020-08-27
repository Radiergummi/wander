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
use Radiergummi\Wander\Exceptions\ResponseErrorException;
use Radiergummi\Wander\Http\MediaType;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Http\Status;
use Radiergummi\Wander\Interfaces\DriverInterface;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
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
    protected DriverInterface $driver;

    protected RequestFactoryInterface $requestFactory;

    protected ResponseFactoryInterface $responseFactory;

    /**
     * Holds all body serializers
     *
     * @var array<string, class-string<Interfaces\SerializerInterface>>
     */
    protected array $bodySerializers = [
        MediaType::APPLICATION_JSON                  => JsonSerializer::class,
        MediaType::TEXT_PLAIN                        => PlainTextSerializer::class,
        MediaType::APPLICATION_X_WWW_FORM_URLENCODED => UrlEncodedSerializer::class,
    ];

    public function __construct(
        ?DriverInterface $driver = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null
    ) {
        $psr17Factory = new Psr17Factory();

        $this->setRequestFactory($requestFactory ?? $psr17Factory);
        $this->setResponseFactory($responseFactory ?? $psr17Factory);
        $this->setDriver($driver ?? new StreamDriver());
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

    public function setRequestFactory(RequestFactoryInterface $requestFactory): void
    {
        $this->requestFactory = $requestFactory;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    public function setResponseFactory(ResponseFactoryInterface $responseFactory): void
    {
        $this->responseFactory = $responseFactory;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    /**
     * Retrieves all supported media type serializers
     *
     * @return array<string, class-string<Interfaces\SerializerInterface>>
     */
    public function getBodySerializers(): array
    {
        return $this->bodySerializers;
    }

    /**
     * Adds a new body serializer
     *
     * @param string $mediaType
     * @param string $serializer
     *
     * @psalm-param class-string<Interfaces\SerializerInterface> $serializer
     *
     * @return $this
     */
    public function addBodySerializer(
        string $mediaType,
        string $serializer
    ): self {
        $this->bodySerializers[$mediaType] = $serializer;

        return $this;
    }

    /**
     * Shorthand to create a GET request
     *
     * @param UriInterface|string $uri
     *
     * @return Context
     */
    public function get($uri): Context
    {
        return $this->createContext(Method::GET, $uri);
    }

    /**
     * Shorthand to create a HEAD request
     *
     * @param UriInterface|string $uri
     *
     * @return Context
     */
    public function head($uri): Context
    {
        return $this->createContext(Method::HEAD, $uri);
    }

    /**
     * Shorthand to create a DELETE request
     *
     * @param UriInterface|string $uri
     *
     * @return Context
     */
    public function options($uri): Context
    {
        return $this->createContext(Method::OPTIONS, $uri);
    }

    /**
     * Shorthand to create a DELETE request
     *
     * @param UriInterface|string $uri
     *
     * @return Context
     */
    public function delete($uri): Context
    {
        return $this->createContext(Method::DELETE, $uri);
    }

    /**
     * Shorthand to create a POST request
     *
     * @param UriInterface|string $uri
     * @param mixed|null          $body
     *
     * @return Context
     */
    public function post($uri, $body = null): Context
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
     * @return Context
     */
    public function put($uri, $body = null): Context
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
     * @return Context
     */
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
        $response = $this->driver->sendRequest($request);

        if (Status::isError($response->getStatusCode())) {
            throw new ResponseErrorException($request, $response);
        }

        return $response;
    }
}
