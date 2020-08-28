<?php

namespace Radiergummi\Wander\Interfaces\Features;

use InvalidArgumentException;
use RangeException;

interface SupportsTimeoutsInterface
{
    /**
     * Configures the client to abort the request after a given timeout. Although
     * the timeout has to be specified in milliseconds, there is no precision
     * requirement for drivers: The timeout amount will be rounded by the driver
     * as appropriate.
     *
     * @param int|null $amount Number of milliseconds after which the request
     *                         shall be aborted. If `null` is passed, the
     *                         timeout will be disabled.
     *
     * @throws InvalidArgumentException If the amount is 0.
     * @throws RangeException If the amount is negative.
     */
    public function setTimeout(?int $amount): void;

    /**
     * Retrieves the request timeout, if configured.
     *
     * @return int
     */
    public function getTimeout(): ?int;
}
