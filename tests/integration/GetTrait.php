<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Interfaces\HttpClientInterface;

use function json_decode;

use const JSON_THROW_ON_ERROR;

trait GetTrait
{
    public function testGetRequest(): void
    {
        $client = $this->getClient();
        $uri = $this->getTestServerUri()->withPath('/loopback');
        $request = $this->createRequest(Method::GET, $uri);
        $response = $client->request($request);

        $this->assertStatusCodeOk($response);
    }

    public function testGetRequestReceivesResponseBody(): void
    {
        $client = $this->getClient();
        $uri = $this->getTestServerUri()->withPath('/loopback');
        $request = $this->createRequest(Method::GET, $uri);
        $response = $client->request($request);
        $responseBody = json_decode(
            $response->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertStatusCodeOk($response);
        $this->assertNotNull($responseBody);
        $this->assertIsArray($responseBody);
        $this->assertArrayHasKey('uri', $responseBody);
        $this->assertArrayHasKey('method', $responseBody);
        $this->assertArrayHasKey('body', $responseBody);
        $this->assertArrayHasKey('headers', $responseBody);
    }

    public function testGetRequestWithQueryParameter(): void
    {
        $client = $this->getClient();
        $uri = $this
            ->getTestServerUri()
            ->withPath('/loopback')
            ->withQuery('foo=bar');
        $request = $this->createRequest(Method::GET, $uri);
        $response = $client->request($request);
        $responseBody = json_decode(
            $response->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertStatusCodeOk($response);
        $this->assertSame('/loopback', $responseBody['uri']);
        $this->assertSame(
            ['foo' => 'bar'],
            $responseBody['query']
        );
    }

    public function testGetRequestIsSentWithoutBody(): void
    {
        $client = $this->getClient();
        $uri = $this->getTestServerUri()->withPath('/loopback');
        $request = $this
            ->createRequest(Method::GET, $uri)
            ->withBody(Stream::create('test'));
        $response = $client->request($request);
        $responseBody = json_decode(
            $response->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertStatusCodeOk($response);
        $this->assertSame('', $responseBody['body']);
    }

    abstract protected function getClient(): HttpClientInterface;

    abstract protected function createRequest(
        string $method,
        UriInterface $uri
    ): RequestInterface;
}
