<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use Radiergummi\Wander\Drivers\StreamDriver;
use Radiergummi\Wander\Interfaces\DriverInterface;

class StreamTest extends AbstractRequestTest
{
    use GetTrait;

    protected function getDriver(): DriverInterface
    {
        return new StreamDriver();
    }
}