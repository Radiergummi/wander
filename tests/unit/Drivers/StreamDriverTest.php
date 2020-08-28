<?php
/** @noinspection PhpComposerExtensionStubsInspection, PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Unit\Drivers;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Radiergummi\Wander\Drivers\CurlDriver;
use Radiergummi\Wander\Drivers\StreamDriver;
use RangeException;

use const CURLOPT_AUTOREFERER;

class StreamDriverTest extends TestCase
{
    private StreamDriver $driver;

    protected function setUp(): void
    {
        $this->driver = new StreamDriver();
    }

    public function testSetTimeout(): void
    {
        $this->assertNull($this->driver->getTimeout());
        $this->driver->setTimeout(42);
        $this->assertEquals(42, $this->driver->getTimeout());
    }

    public function testSetTimeoutBailsForNegativeAmounts(): void
    {
        $this->expectException(RangeException::class);
        $this->driver->setTimeout(-42);
    }

    public function testSetTimeoutBailsForZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->driver->setTimeout(0);
    }

    public function testSetMaximumRedirects(): void
    {
        $this->assertNull($this->driver->getMaximumRedirects());
        $this->driver->setMaximumRedirects(42);
        $this->assertEquals(
            42,
            $this->driver->getMaximumRedirects()
        );
    }

    public function testSetMaximumRedirectsAcceptsZero(): void
    {
        $this->assertNull($this->driver->getMaximumRedirects());
        $this->driver->setMaximumRedirects(0);
        $this->assertEquals(
            0,
            $this->driver->getMaximumRedirects(),
            'Driver would not store 0 maximum redirects correctly'
        );
    }

    public function testSetMaximumRedirectsBailsForNegativeNumber(): void
    {
        $this->expectException(RangeException::class);
        $this->driver->setMaximumRedirects(-42);
    }

    public function testConstructorAcceptsDefaultCurlOptions(): void
    {
        $driver = new CurlDriver([
            CURLOPT_AUTOREFERER => true,
        ]);

        $this->assertSame(
            [CURLOPT_AUTOREFERER => true],
            $driver->getDefaultOptions(),
            'Default options would not be stored correctly'
        );
    }

    public function testFollowRedirects(): void
    {
        $this->assertTrue($this->driver->isFollowRedirectsEnabled());
        $this->driver->followRedirects(false);
        $this->assertFalse($this->driver->isFollowRedirectsEnabled());
        $this->driver->followRedirects();
        $this->assertTrue($this->driver->isFollowRedirectsEnabled());
        $this->driver->followRedirects(false);
        $this->assertFalse($this->driver->isFollowRedirectsEnabled());
        $this->driver->followRedirects(true);
        $this->assertTrue($this->driver->isFollowRedirectsEnabled());
    }
}
