<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use Radiergummi\Wander\Drivers\CurlDriver;
use Radiergummi\Wander\Interfaces\DriverInterface;

class CurlTest extends AbstractRequestTest
{
    use GetTrait;

    protected static string $serverListenAddress = '127.0.0.1:9998';

    protected function getDriver(): DriverInterface
    {
        return new CurlDriver();
    }
}
