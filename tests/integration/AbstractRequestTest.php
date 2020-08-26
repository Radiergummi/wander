<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Http\Status;
use Radiergummi\Wander\Interfaces\DriverInterface;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use Radiergummi\Wander\Wander;
use Symfony\Component\Process\Process;

abstract class AbstractRequestTest extends TestCase
{
    private static ?Process $serverProcess;

    protected static string $serverListenAddress = '127.0.0.1:9999';

    protected RequestFactoryInterface $requestFactory;

    protected ResponseFactoryInterface $responseFactory;

    protected UriFactoryInterface $uriFactory;

    public static function setUpBeforeClass(): void
    {
        self::$serverProcess = new Process([
            'php',
            '-S',
            self::$serverListenAddress,
            '-t',
            realpath(__DIR__ . '/../_fixtures/'),
            realpath(__DIR__ . '/../_fixtures/responder.php'),
        ]);
        self::$serverProcess->disableOutput();
        self::$serverProcess->start();

        usleep(1000000);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$serverProcess && self::$serverProcess->isRunning()) {
            self::$serverProcess->stop();
        }
    }

    protected function setUp(): void
    {
        $factory = new Psr17Factory();

        $this->uriFactory = $factory;
        $this->requestFactory = $factory;
        $this->responseFactory = $factory;
    }

    protected function getTestServerOutput(): array
    {
        return [
            self::$serverProcess->getOutput(),
            self::$serverProcess->getErrorOutput()
        ];
    }

    protected function getTestServerUri(): UriInterface
    {
        return $this->uriFactory
            ->createUri(self::$serverListenAddress)
            ->withScheme('http');
    }

    protected function getClient(): HttpClientInterface
    {
        return new Wander(
            $this->getDriver(),
            $this->requestFactory,
            $this->responseFactory
        );
    }

    protected function createRequest(string $method, UriInterface $uri): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $uri);
    }

    protected function assertStatusCodeOk(
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $this->assertStatusCode(
            Status::OK,
            $response,
            $message
        );
    }

    protected function assertStatusCode(
        int $expectedStatusCode,
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $statusCode = $response->getStatusCode();
        $message = $message ?? "Expected status code {$expectedStatusCode}, actual is {$statusCode}";

        $this->assertSame(
            $expectedStatusCode,
            $statusCode,
            $message
        );
    }

    protected function assertResponseBodyEmpty(
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $this->assertResponseBodyEquals(
            '',
            $response,
            $message
        );
    }

    protected function assertResponseBodyNotEmpty(
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $this->assertResponseBodyNotEquals(
            '',
            $response,
            $message
        );
    }

    protected function assertResponseBodyEquals(
        string $expectedBody,
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $this->assertSame(
            $expectedBody,
            $response->getBody()->getContents(),
            $message ?? ''
        );
    }

    protected function assertResponseBodyNotEquals(
        string $expectedBody,
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $this->assertNotSame(
            $expectedBody,
            $response->getBody()->getContents(),
            $message ?? ''
        );
    }

    abstract protected function getDriver(): DriverInterface;
}
