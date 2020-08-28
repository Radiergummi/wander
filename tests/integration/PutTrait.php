<?php
/**
 * @noinspection PhpUnused
 * @noinspection UnknownInspectionInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Interfaces\HttpClientInterface;

trait PutTrait
{
    public function testPutRequest(): void
    {
        $client = $this->getClient();
        $uri = $this->getTestServerUri()->withPath('/loopback');
        $request = $this->createRequest(Method::PUT, $uri);
        $response = $client->request($request);

        $this->assertStatusCodeOk($response);
    }

    public function testPutRequestWithBody(): void
    {
        $client = $this->getClient();
        $uri = $this->getTestServerUri()->withPath('/body');
        $request = $this
            ->createRequest(Method::PUT, $uri)
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
