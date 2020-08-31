<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace Radiergummi\Wander\Tests\Unit;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Radiergummi\Wander\Context\ResponseContext;
use Radiergummi\Wander\Http\MediaType;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use Radiergummi\Wander\SerializerRegistry;
use Radiergummi\Wander\Serializers\JsonSerializer;

class ResponseContextTest extends TestCase
{
    private HttpClientInterface $client;

    private ResponseContext $context;

    private Psr17Factory $responseFactory;

    public function testRetrievesResponseInstance(): void
    {
        $response = $this->responseFactory->createResponse();
        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame($response, $context->getResponse());
    }

    public function testRetrievesStatusCode(): void
    {
        $response = $this->responseFactory->createResponse(400);
        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(400, $context->getStatusCode());
    }

    public function testRetrievesInvalidStatusCode(): void
    {
        $response = $this->responseFactory->createResponse(9999);
        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(9999, $context->getStatusCode());
    }

    public function testRetrievesReasonPhrase(): void
    {
        $response = $this->responseFactory->createResponse(400);
        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(
            'Bad Request',
            $context->getReasonPhrase()
        );
    }

    public function testRetrievesCustomReasonPhrase(): void
    {
        $response = $this->responseFactory->createResponse(
            400,
            'Foo'
        );
        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(
            'Foo',
            $context->getReasonPhrase()
        );
    }

    public function testRetrievesProtocolVersion(): void
    {
        $response = $this->responseFactory
            ->createResponse()
            ->withProtocolVersion('2.0');
        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(
            '2.0',
            $context->getProtocolVersion()
        );
    }

    public function testRetrievesCustomProtocolVersion(): void
    {
        $response = $this->responseFactory
            ->createResponse()
            ->withProtocolVersion('foo');
        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(
            'foo',
            $context->getProtocolVersion()
        );
    }

    public function testRetrievesMultipleHeaders(): void
    {
        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Host', 'example.org')
            ->withHeader('Foo', 'Bar');

        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(
            ['Host' => ['example.org'], 'Foo' => ['Bar']],
            $context->getHeaders()
        );
    }

    public function testRetrievesHeadersByName(): void
    {
        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Foo', ['bar', 'baz', 'quz']);

        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(
            ['bar', 'baz', 'quz'],
            $context->getHeader('foo')
        );
    }

    public function testReturnsEmptyArrayForMissingHeaders(): void
    {
        $this->assertSame(
            [],
            $this->context->getHeader('Missing')
        );
    }

    public function testRetrievesHeadersLinesByName(): void
    {
        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Foo', ['bar', 'baz', 'quz']);

        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(
            ['bar', 'baz', 'quz'],
            $context->getHeader('foo')
        );
        $this->assertSame(
            'bar, baz, quz',
            $context->getHeaderLine('foo')
        );
    }

    public function testReturnsEmptyStringForMissingHeaderLines(): void
    {
        $this->assertSame(
            '',
            $this->context->getHeaderLine('Missing')
        );
    }

    public function testRetrievesBody(): void
    {
        $stream = Stream::create();
        $response = $this->responseFactory
            ->createResponse()
            ->withBody($stream);

        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame($stream, $context->getBody());
    }

    public function testRetrievesParsedBody(): void
    {
        $stream = Stream::create('{"foo":"bar"}');
        $stream->rewind();
        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);

        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(
            ['foo' => 'bar'],
            $context->getParsedBody()
        );
    }

    public function testBodySerializerIsResolvedWithoutEncoding(): void
    {
        $stream = Stream::create('{"foo":"bar"}');
        $stream->rewind();
        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withBody($stream);

        $context = new ResponseContext(
            $this->client,
            $response
        );

        $this->assertSame(
            ['foo' => 'bar'],
            $context->getParsedBody()
        );
    }

    protected function setUp(): void
    {
        $this->responseFactory = new Psr17Factory();
        $this->client = $this->createMock(
            HttpClientInterface::class
        );
        $serializers = new SerializerRegistry();
        $serializers->register(
            MediaType::APPLICATION_JSON,
            new JsonSerializer()
        );
        $this->client
            ->method('getSerializerRegistry')
            ->willReturn($serializers);

        $response = $this->responseFactory->createResponse(200);

        $this->context = new ResponseContext(
            $this->client,
            $response
        );
    }
}
