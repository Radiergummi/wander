<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Unit;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Radiergummi\Wander\Context\RequestContext;
use Radiergummi\Wander\Http\MediaType;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use Radiergummi\Wander\SerializerRegistry;
use Radiergummi\Wander\Serializers\JsonSerializer;

use function base64_encode;

class RequestContextTest extends TestCase
{
    private HttpClientInterface $client;

    private RequestInterface $request;

    private RequestContext $context;

    public function testCreatesInstances(): void
    {
        $this->assertInstanceOf(RequestContext::class, $this->context);
    }

    public function testRetrievesRequestInstance(): void
    {
        $this->assertSame($this->request, $this->context->getRequest());
    }

    public function testAllowsOverridingRequestInstance(): void
    {
        $newRequest = new Request('foo', 'bar');

        $this->assertSame($this->request, $this->context->getRequest());
        $this->context->setRequest($newRequest);
        $this->assertSame($newRequest, $this->context->getRequest());
    }

    public function testRetrievesRequestMethod(): void
    {
        $this->assertSame('GET', $this->context->getMethod());
    }

    public function testSetsRequestMethod(): void
    {
        $this->assertSame('GET', $this->context->getMethod());
        $this->context->withMethod('POST');
        $this->assertSame('POST', $this->context->getMethod());
    }

    public function testWithMethodIsChainable(): void
    {
        $returnValue = $this->context->withMethod('POST');
        $this->assertSame($this->context, $returnValue);
    }

    public function testRetrievesRequestUri(): void
    {
        $this->assertSame('', $this->context->getUri()->getPath());
    }

    public function testSetsRequestUri(): void
    {
        $this->assertSame('', $this->context->getUri()->getPath());
        $this->context->withUri(new Uri('https://example.com/foo'));
        $this->assertSame('/foo', $this->context->getUri()->getPath());
    }

    public function testWithUriIsChainable(): void
    {
        $returnValue = $this->context->withUri(new Uri(''));
        $this->assertSame($this->context, $returnValue);
    }

    public function testRetrievesQueryString(): void
    {
        $this->assertSame('', $this->context->getQueryString());
    }

    public function testSetsQueryString(): void
    {
        $this->assertSame('', $this->context->getQueryString());
        $this->context->withQueryString('foo=bar&baz=quz');
        $this->assertSame('foo=bar&baz=quz', $this->context->getQueryString());
    }

    public function testSetsQueryStringOnUri(): void
    {
        $this->assertSame('', $this->context->getUri()->getQuery());
        $this->context->withQueryString('foo=bar&baz=quz');
        $this->assertSame('foo=bar&baz=quz', $this->context->getUri()->getQuery());
    }

    public function testWithQueryStringIsChainable(): void
    {
        $returnValue = $this->context->withQueryString('');
        $this->assertSame($this->context, $returnValue);
    }

    public function testRetrievesQueryParameters(): void
    {
        $this->assertSame([], $this->context->getQueryParameters());
    }

    public function testSetsQueryParameters(): void
    {
        $this->assertSame([], $this->context->getQueryParameters());
        $this->context->withQueryString('foo=bar&baz=quz');
        $this->assertSame('foo=bar&baz=quz', $this->context->getQueryString());
        $this->assertSame(
            [
                'foo' => 'bar',
                'baz' => 'quz',
            ],
            $this->context->getQueryParameters()
        );
    }

    public function testSetsQueryParametersOnQueryString(): void
    {
        $this->assertSame([], $this->context->getQueryParameters());
        $this->context->withQueryParameters(
            [
                'foo' => 'bar',
                'baz' => 'quz',
            ]
        );
        $this->assertSame('foo=bar&baz=quz', $this->context->getQueryString());
        $this->assertSame('foo=bar&baz=quz', $this->context->getUri()->getQuery());
    }

    public function testWithQueryParametersIsChainable(): void
    {
        $returnValue = $this->context->withQueryParameters([]);
        $this->assertSame($this->context, $returnValue);
    }

    public function testRetrievesQueryParameterByName(): void
    {
        $this->assertNull($this->context->getQueryParameter('foo'));
        $this->context->withQueryParameter('foo', 'bar');
        $this->assertSame('bar', $this->context->getQueryParameter('foo'));
    }

    public function testReturnsNullForMissingParameters(): void
    {
        $this->assertNull($this->context->getQueryParameter('foo'));
    }

    public function testSetsStringQueryParameter(): void
    {
        $this->context->withQueryParameter('foo', 'bar');
        $this->assertSame('bar', $this->context->getQueryParameter('foo'));
        $this->assertSame(['foo' => 'bar'], $this->context->getQueryParameters());
        $this->assertSame('foo=bar', $this->context->getQueryString());
    }

    public function testSetsIntegerQueryParameter(): void
    {
        $this->context->withQueryParameter('foo', 42);
        $this->assertSame('42', $this->context->getQueryParameter('foo'));
        $this->assertSame(['foo' => '42'], $this->context->getQueryParameters());
        $this->assertSame('foo=42', $this->context->getQueryString());
    }

    public function testSetsFloatQueryParameter(): void
    {
        $this->context->withQueryParameter('foo', 3.14);
        $this->assertSame('3.14', $this->context->getQueryParameter('foo'));
        $this->assertSame(['foo' => '3.14'], $this->context->getQueryParameters());
        $this->assertSame('foo=3.14', $this->context->getQueryString());
    }

    public function testSetsBooleanQueryParameter(): void
    {
        $this->context->withQueryParameter('foo', true);
        $this->assertSame('1', $this->context->getQueryParameter('foo'));
        $this->assertSame(['foo' => '1'], $this->context->getQueryParameters());
        $this->assertSame('foo=1', $this->context->getQueryString());
    }

    public function testSetsNullQueryParameter(): void
    {
        $this->context->withQueryParameter('foo', null);
        $this->assertNull($this->context->getQueryParameter('foo'));
        $this->assertSame([], $this->context->getQueryParameters());
        $this->assertSame('', $this->context->getQueryString());
    }

    public function testSetsQueryParameterOnQueryString(): void
    {
        $this->context->withQueryParameter('foo', 'bar');
        $this->assertSame('foo=bar', $this->context->getQueryString());
        $this->assertSame('foo=bar', $this->context->getUri()->getQuery());
    }

    public function testWithQueryParameterIsChainable(): void
    {
        $returnValue = $this->context->withQueryParameter('foo', 'bar');
        $this->assertSame($this->context, $returnValue);
    }

    public function testRemovesQueryParameters(): void
    {
        $this->context->withQueryParameter('foo', 'bar');
        $this->assertSame('bar', $this->context->getQueryParameter('foo'));
        $this->context->withoutQueryParameter('foo');
        $this->assertNull($this->context->getQueryParameter('foo'));
    }

    public function testIgnoresMissingParametersOnRemovingQueryParameters(): void
    {
        $this->context->withQueryParameters(['foo' => 1, 'bar' => 2]);
        $this->assertSame(['foo' => '1', 'bar' => '2'], $this->context->getQueryParameters());
        $this->context->withoutQueryParameter('baz');
        $this->assertSame(['foo' => '1', 'bar' => '2'], $this->context->getQueryParameters());
    }

    public function testWithoutQueryParameterIsChainable(): void
    {
        $returnValue = $this->context->withoutQueryParameter('foo');
        $this->assertSame($this->context, $returnValue);
    }

    public function testRetrievesMultipleHeaders(): void
    {
        $this->assertSame(['Host' => ['example.com']], $this->context->getHeaders());
        $request = new Request('GET', 'https://example.org', ['Foo' => ['bar']]);
        $this->context->setRequest($request);
        $this->assertSame(['Host' => ['example.org'], 'Foo' => ['bar']], $this->context->getHeaders());
    }

    public function testRetrievesHeadersByName(): void
    {
        $this->assertSame(['example.com'], $this->context->getHeader('Host'));
        $this->context->withHeader('foo', ['bar', 'baz']);
        $this->assertSame(['bar', 'baz'], $this->context->getHeader('foo'));
    }

    public function testReturnsEmptyArrayForMissingHeaders(): void
    {
        $this->assertSame([], $this->context->getHeader('Missing'));
    }

    public function testRetrievesHeadersLinesByName(): void
    {
        $this->assertSame('example.com', $this->context->getHeaderLine('Host'));
        $this->context->withHeader('foo', ['bar', 'baz']);
        $this->assertSame('bar, baz', $this->context->getHeaderLine('foo'));
    }

    public function testReturnsEmptyStringForMissingHeaderLines(): void
    {
        $this->assertSame('', $this->context->getHeaderLine('Missing'));
    }

    public function testSetsHeaders(): void
    {
        $this->assertSame(['Host' => ['example.com']], $this->context->getHeaders());
        $this->context->withHeader('Foo', 'bar');
        $this->assertSame(['Host' => ['example.com'], 'Foo' => ['bar']], $this->context->getHeaders());
    }

    public function testSetsHeadersWithMultipleValues(): void
    {
        $this->assertSame(['Host' => ['example.com']], $this->context->getHeaders());
        $this->context->withHeader('Foo', ['bar', 'baz', 'quz']);
        $this->assertSame(['Host' => ['example.com'], 'Foo' => ['bar', 'baz', 'quz']], $this->context->getHeaders());
    }

    public function testReplacesExistingHeadersByDefault(): void
    {
        $this->assertSame(['Host' => ['example.com']], $this->context->getHeaders());
        $this->context->withHeader('Foo', 'bar');
        $this->assertSame(['Host' => ['example.com'], 'Foo' => ['bar']], $this->context->getHeaders());
        $this->context->withHeader('Foo', 'quz');
        $this->assertSame(['Host' => ['example.com'], 'Foo' => ['quz']], $this->context->getHeaders());
    }

    public function testAppendsHeaders(): void
    {
        $this->assertSame(['Host' => ['example.com']], $this->context->getHeaders());
        $this->context->withHeader('Foo', 'bar');
        $this->assertSame(['Host' => ['example.com'], 'Foo' => ['bar']], $this->context->getHeaders());
        $this->context->withHeader('Foo', 'quz', true);
        $this->assertSame(['Host' => ['example.com'], 'Foo' => ['bar', 'quz']], $this->context->getHeaders());
    }

    public function testWithHeaderIsChainable(): void
    {
        $returnValue = $this->context->withHeader('foo', 'bar');
        $this->assertSame($this->context, $returnValue);
    }

    public function testSetsMultipleHeaders(): void
    {
        $this->assertSame(['Host' => ['example.com']], $this->context->getHeaders());
        $this->context->withHeaders(['Foo' => '42', 'Bar' => ['3.14', '2.718']]);
        $this->assertSame(
            ['Host' => ['example.com'], 'Foo' => ['42'], 'Bar' => ['3.14', '2.718']],
            $this->context->getHeaders()
        );
    }

    public function testReplacesMultipleExistingHeadersByDefault(): void
    {
        $this->assertSame(['Host' => ['example.com']], $this->context->getHeaders());
        $this->context->withHeaders(['Foo' => 'bar', 'Baz' => 'quz']);
        $this->assertSame(
            ['Host' => ['example.com'], 'Foo' => ['bar'], 'Baz' => ['quz']],
            $this->context->getHeaders()
        );
        $this->context->withHeaders(['Foo' => '123', 'Baz' => '456']);
        $this->assertSame(
            ['Host' => ['example.com'], 'Foo' => ['123'], 'Baz' => ['456']],
            $this->context->getHeaders()
        );
    }

    public function testAppendsMultipleHeaders(): void
    {
        $this->assertSame(['Host' => ['example.com']], $this->context->getHeaders());
        $this->context->withHeaders(['Foo' => 'bar', 'Baz' => 'quz']);
        $this->assertSame(
            ['Host' => ['example.com'], 'Foo' => ['bar'], 'Baz' => ['quz']],
            $this->context->getHeaders()
        );
        $this->context->withHeaders(['Foo' => '123', 'Baz' => '456'], true);
        $this->assertSame(
            ['Host' => ['example.com'], 'Foo' => ['bar', '123'], 'Baz' => ['quz', '456']],
            $this->context->getHeaders()
        );
    }

    public function testWithHeadersIsChainable(): void
    {
        $returnValue = $this->context->withHeaders([]);
        $this->assertSame($this->context, $returnValue);
    }

    public function testRemovesHeaders(): void
    {
        $this->context->withHeader('Foo', 'bar');
        $this->assertSame(['Host' => ['example.com'], 'Foo' => ['bar']], $this->context->getHeaders());
        $this->context->withoutHeader('Foo');
        $this->assertSame(['Host' => ['example.com']], $this->context->getHeaders());
    }

    public function testWithoutHeadersIsChainable(): void
    {
        $returnValue = $this->context->withoutHeader('');
        $this->assertSame($this->context, $returnValue);
    }

    public function testAddsAuthorization(): void
    {
        $this->context->withAuthorization('Foo', 'bar');
        $this->assertSame('Foo bar', $this->context->getHeaderLine('Authorization'));
    }

    public function testDoesNotDuplicateAuthorization(): void
    {
        $this->context->withAuthorization('Foo', 'bar');
        $this->assertSame('Foo bar', $this->context->getHeaderLine('Authorization'));
        $this->context->withAuthorization('Foo', 'bar');
        $this->assertSame('Foo bar', $this->context->getHeaderLine('Authorization'));
    }

    public function testWithAuthorizationIsChainable(): void
    {
        $returnValue = $this->context->withAuthorization('', '');
        $this->assertSame($this->context, $returnValue);
    }

    public function testAddsBasicAuthorization(): void
    {
        $this->context->withBasicAuthorization('foo', 'bar');
        $encoded = base64_encode("foo:bar");
        $this->assertSame(
            "Basic {$encoded}",
            $this->context->getHeaderLine('Authorization')
        );
    }

    public function testWithBasicAuthorizationIsChainable(): void
    {
        $returnValue = $this->context->withBasicAuthorization(
            '',
            ''
        );
        $this->assertSame($this->context, $returnValue);
    }

    public function testAddsBearerAuthorization(): void
    {
        $this->context->withBearerAuthorization('foo');
        $this->assertSame(
            'Bearer foo',
            $this->context->getHeaderLine('Authorization')
        );
    }

    public function testWithBearerAuthorizationIsChainable(): void
    {
        $returnValue = $this->context->withBearerAuthorization('');
        $this->assertSame($this->context, $returnValue);
    }

    public function testAddsContentTypeHeader(): void
    {
        $this->context->withContentType('foo');
        $this->assertSame(
            'foo',
            $this->context->getHeaderLine('Content-Type')
        );
    }

    public function testRetrievesContentTypeHeader(): void
    {
        $this->assertNull($this->context->getContentType());
        $this->context->withContentType('foo/bar');
        $this->assertSame(
            'foo/bar',
            $this->context->getContentType()
        );
    }

    public function testRetrievesContentTypeHeaderWithoutEncoding(): void
    {
        $this->assertNull($this->context->getContentType());
        $this->context->withContentType(
            'application/json; charset=utf-8'
        );

        $this->assertSame(
            'application/json; charset=utf-8',
            $this->context->getContentType()
        );

        $this->assertSame(
            'application/json',
            $this->context->getContentType(true)
        );
    }

    public function testWithContentTypeIsChainable(): void
    {
        $returnValue = $this->context->withContentType('');
        $this->assertSame($this->context, $returnValue);
    }

    public function testAddsJsonContentTypeHeader(): void
    {
        $this->context->asJson();
        $this->assertSame(
            'application/json',
            $this->context->getHeaderLine('Content-Type')
        );
    }

    public function testAsJsonIsChainable(): void
    {
        $returnValue = $this->context->asJson();
        $this->assertSame($this->context, $returnValue);
    }

    public function testAddsXmlContentTypeHeader(): void
    {
        $this->context->asXml();
        $this->assertSame(
            'text/xml',
            $this->context->getHeaderLine('Content-Type')
        );
    }

    public function testAsXmlIsChainable(): void
    {
        $returnValue = $this->context->asXml();
        $this->assertSame($this->context, $returnValue);
    }

    public function testAddsPlainTextContentTypeHeader(): void
    {
        $this->context->asPlainText();
        $this->assertSame(
            'text/plain',
            $this->context->getHeaderLine('Content-Type')
        );
    }

    public function testAsPlainTextIsChainable(): void
    {
        $returnValue = $this->context->asPlainText();
        $this->assertSame($this->context, $returnValue);
    }

    public function testAddsBody(): void
    {
        $this->assertFalse($this->context->hasBody());
        $this->context->withBody('foo');
        $this->assertTrue($this->context->hasBody());
    }

    public function testRetrievesBody(): void
    {
        $this->assertNull($this->context->getBody());
        $this->context->withBody('foo');
        $this->assertSame('foo', $this->context->getBody());
    }

    public function testBodyIsSerializedOnDispatch(): void
    {
        $this->context->withBody('foo');
        $this->context->withContentType(
            MediaType::APPLICATION_JSON
        );
        $this->context->run();
        $this->assertSame(
            '"foo"',
            (string)$this->context->getRequest()->getBody()
        );
    }

    public function testBodySerializerIsResolvedWithoutEncoding(): void
    {
        $this->context->withBody('foo');
        $this->context->withContentType(
            'application/json; charset=utf-8'
        );
        $this->context->run();
        $this->assertSame(
            '"foo"',
            (string)$this->context->getRequest()->getBody()
        );
    }

    public function testWithBodyIsChainable(): void
    {
        $returnValue = $this->context->withBody('');
        $this->assertSame($this->context, $returnValue);
    }

    public function testPassesRequestToClient(): void
    {
        $this->client
            ->expects($this->once())
            ->method('request');
        $this->context->run();
    }

    protected function setUp(): void
    {
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

        $this->request = new Request('GET', 'https://example.com');
        $this->context = new RequestContext(
            $this->client,
            $this->request
        );
    }
}
