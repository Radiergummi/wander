<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use Radiergummi\Wander\Drivers\StreamDriver;
use Radiergummi\Wander\Interfaces\DriverInterface;

class StreamTest extends AbstractRequestTest
{
    use GetTrait;
    use PostTrait;
    use PutTrait;
    use PatchTrait;
    use ErrorHandlingTrait;

    protected function getDriver(): DriverInterface
    {
        return new StreamDriver();
    }
}
