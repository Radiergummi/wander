<?php

namespace Radiergummi\Wander\Drivers\Features;

use InvalidArgumentException;
use RangeException;

trait TimeoutTrait
{
    private ?int $timeout = null;

    /**
     * @inheritDoc
     */
    public function setTimeout(?int $amount): void
    {
        if ($amount === null || $amount > 0) {
            $this->timeout = $amount;

            return;
        }

        if ($amount === 0) {
            throw new InvalidArgumentException(
                'Use NULL to disable the timeout'
            );
        }

        throw new RangeException(
            'Timeout amount must be a positive integer'
        );
    }

    /**
     * @inheritDoc
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }
}
