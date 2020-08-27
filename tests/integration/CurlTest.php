<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use Radiergummi\Wander\Drivers\CurlDriver;
use Radiergummi\Wander\Interfaces\DriverInterface;

class CurlTest extends AbstractRequestTest
{
    use GetTrait;
    use PostTrait;
    use ErrorHandlingTrait;

    protected function getDriver(): DriverInterface
    {
        return new CurlDriver();
    }
}
