<?php

namespace Radiergummi\Wander\Context;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Radiergummi\Wander\Http\MediaType;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use Radiergummi\Wander\Serializers\PlainTextSerializer;
use RuntimeException;

class ResponseContext
{
    use ContextTrait;

    protected HttpClientInterface $client;

    protected ResponseInterface $response;

    /**
     * Holds unserialized response body data.
     *
     * @var mixed|null
     */
    protected $body = null;

    public function __construct(
        HttpClientInterface $client,
        ResponseInterface $response
    ) {
        $this->client = $client;
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        return $this->getResponse()->getStatusCode();
    }

    public function getReasonPhrase(): string
    {
        return $this->getResponse()->getReasonPhrase();
    }

    public function getProtocolVersion(): string
    {
        return $this->getResponse()->getProtocolVersion();
    }

    public function getBody(): StreamInterface
    {
        return $this->getResponse()->getBody();
    }

    /**
     * @return mixed
     * @throws RuntimeException
     */
    public function getParsedBody()
    {
        if ( ! $this->body) {
            $this->parseBody();
        }

        return $this->body;
    }

    /**
     * @throws RuntimeException
     */
    protected function parseBody(): void
    {
        // Resolve the appropriate serializer by resolving the media  type from
        // the response instance.
        $contentType = $this->getContentType(true)
                       ?? MediaType::TEXT_PLAIN;

        $serializer = $this->client
                          ->getSerializerRegistry()
                          ->resolve($contentType)
                      ?? new PlainTextSerializer();

        // Let the serializer extract the body from the response. Passing the
        // response makes it possible to review additional headers if necessary.
        $this->body = $serializer->extract($this->response);
    }

    /**
     * @inheritDoc
     */
    final protected function getMessage(): MessageInterface
    {
        return $this->response;
    }
}
