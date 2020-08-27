<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Unit;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Radiergummi\Wander\Context;
use Radiergummi\Wander\Exceptions\ResponseErrorException;
use Radiergummi\Wander\Interfaces\DriverInterface;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use Radiergummi\Wander\Wander;

class WanderTest extends TestCase
{
    public function testCreatesInstances(): void
    {
        $wander = new Wander();

        $this->assertInstanceOf(Wander::class, $wander);
        $this->assertInstanceOf(HttpClientInterface::class, $wander);
    }

    public function testCreatesInstancesWithDriver(): void
    {
        /** @var MockObject&DriverInterface $mockDriver */
        $mockDriver = $this->createMockDriver();
        $wander = new Wander($mockDriver);

        $this->assertInstanceOf(Wander::class, $wander);
        $this->assertInstanceOf(HttpClientInterface::class, $wander);
        $this->assertSame($wander->getDriver(), $mockDriver);
    }

    public function testCreatesInstancesWithRequestFactory(): void
    {
        /** @var MockObject&DriverInterface $mockDriver */
        $mockDriver = $this->createMockDriver();
        $requestFactory = new Psr17Factory();
        $wander = new Wander($mockDriver, $requestFactory);

        $this->assertInstanceOf(Wander::class, $wander);
        $this->assertInstanceOf(HttpClientInterface::class, $wander);
        $this->assertSame($wander->getDriver(), $mockDriver);
        $this->assertSame($wander->getRequestFactory(), $requestFactory);
    }

    public function testCreatesInstancesWithResponseFactory(): void
    {
        /** @var MockObject&DriverInterface $mockDriver */
        $mockDriver = $this->createMockDriver();
        $requestFactory = $responseFactory = new Psr17Factory();
        $wander = new Wander(
            $mockDriver,
            $requestFactory,
            $responseFactory
        );

        $this->assertInstanceOf(Wander::class, $wander);
        $this->assertInstanceOf(HttpClientInterface::class, $wander);
        $this->assertSame($wander->getDriver(), $mockDriver);
        $this->assertSame($wander->getRequestFactory(), $requestFactory);
        $this->assertSame($wander->getResponseFactory(), $responseFactory);
    }

    public function testCreatesGetContexts(): void
    {
        $wander = new Wander();
        $context = $wander->get('https://example.com');

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testCreatesHeadContexts(): void
    {
        $wander = new Wander();
        $context = $wander->head('https://example.com');

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testCreatesDeleteContexts(): void
    {
        $wander = new Wander();
        $context = $wander->delete('https://example.com');

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testCreatesPostContexts(): void
    {
        $wander = new Wander();
        $context = $wander->post('https://example.com');

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testCreatesPostContextsWithBody(): void
    {
        $wander = new Wander();
        $context = $wander->post(
            'https://example.com',
            [
                'foo' => 'bar',
            ]
        );

        $this->assertInstanceOf(Context::class, $context);
        $this->assertTrue($context->hasBody());
    }

    public function testCreatesPutContexts(): void
    {
        $wander = new Wander();
        $context = $wander->put('https://example.com');

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testCreatesPutContextsWithBody(): void
    {
        $wander = new Wander();
        $context = $wander->put(
            'https://example.com',
            [
                'foo' => 'bar',
            ]
        );

        $this->assertInstanceOf(Context::class, $context);
        $this->assertTrue($context->hasBody());
    }

    public function testCreatesPatchContexts(): void
    {
        $wander = new Wander();
        $context = $wander->patch('https://example.com');

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testCreatesPatchContextsWithBody(): void
    {
        $wander = new Wander();
        $context = $wander->patch(
            'https://example.com',
            [
                'foo' => 'bar',
            ]
        );

        $this->assertInstanceOf(Context::class, $context);
        $this->assertTrue($context->hasBody());
    }

    public function testCreatesOptionsContexts(): void
    {
        $wander = new Wander();
        $context = $wander->options('https://example.com');

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testCreatesCustomContexts(): void
    {
        $wander = new Wander();
        $context = $wander->createContext(
            'test',
            'https://example.com'
        );

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testCreatesCustomContextsFromRequestInstances(): void
    {
        $wander = new Wander();
        $requestFactory = new Psr17Factory();
        $request = $requestFactory->createRequest(
            'test',
            'https://example.com'
        );
        $context = $wander->createContextFromRequest($request);

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testExecutesRequests(): void
    {
        /** @var MockObject&DriverInterface $mockDriver */
        $mockDriver = $this->createMockDriver();
        $wander = new Wander($mockDriver);
        $requestFactory = new Psr17Factory();
        $request = $requestFactory->createRequest(
            'test',
            'https://example.com'
        );

        $mockDriver
            ->method('sendRequest')
            ->willReturn(new Response(200));

        $response = $wander->request($request);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $response
        );
    }

    public function testThrowsResponseErrorForErrorStatus(): void
    {
        /** @var MockObject&DriverInterface $mockDriver */
        $mockDriver = $this->createMockDriver();
        $wander = new Wander($mockDriver);
        $requestFactory = new Psr17Factory();
        $request = $requestFactory->createRequest(
            'test',
            'https://example.com'
        );

        $mockDriver
            ->method('sendRequest')
            ->willReturn(new Response(400));

        $this->expectException(ResponseErrorException::class);
        $wander->request($request);
    }

    private function createMockDriver(): MockObject
    {
        return $this->createMock(DriverInterface::class);
    }
}
