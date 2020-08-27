<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace Radiergummi\Wander\Tests\Integration;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Exceptions\UnresolvableHostException;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Interfaces\HttpClientInterface;

trait ErrorHandlingTrait
{
    public function testThrowsUnresolvableHostException(): void
    {
        $request = $this->createRequest(
            Method::GET,
            new Uri('https://test.invalid')
        );

        $this->expectException(UnresolvableHostException::class);

        $this->getClient()->request($request);
    }

    abstract protected function getClient(): HttpClientInterface;

    abstract protected function createRequest(
        string $method,
        UriInterface $uri
    ): RequestInterface;
}
