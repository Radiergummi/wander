<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Interfaces\HttpClientInterface;

trait PostTrait
{
    public function testPostRequest(): void
    {
        $client = $this->getClient();
        $uri = $this->getTestServerUri()->withPath('/loopback');
        $request = $this->createRequest(Method::POST, $uri);
        $response = $client->request($request);

        $this->assertStatusCodeOk($response);
    }

    public function testPostRequestWithPlainTextBody(): void
    {
        $client = $this->getClient();
        $uri = $this->getTestServerUri()->withPath('/body');
        $request = $this
            ->createRequest(Method::POST, $uri)
            ->withBody(Stream::create('test'));
        $response = $client->request($request);

        $this->assertStatusCodeOk($response);
        $this->assertResponseBodyEquals(
            'test',
            $response
        );
    }

    abstract protected function getClient(): HttpClientInterface;

    abstract protected function createRequest(
        string $method,
        UriInterface $uri
    ): RequestInterface;
}
