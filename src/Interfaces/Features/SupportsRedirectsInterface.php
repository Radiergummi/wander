<?php

namespace Radiergummi\Wander\Interfaces\Features;

use RangeException;

interface SupportsRedirectsInterface
{
    /**
     * Configures the client to follow redirects.
     *
     * @param bool $followRedirects
     */
    public function followRedirects(bool $followRedirects): void;

    /**
     * Checks whether the client is configured to follow redirects.
     *
     * @return bool
     */
    public function isFollowRedirectsEnabled(): bool;

    /**
     * Sets the maximum number of redirects the driver will follow before
     * aborting the request. This setting has no effect if the driver is not
     * configured to follow redirects in the first place.
     * If the driver should not impose an upper limit on the number of redirects
     * followed, this must be set to `NULL`.
     *
     * @param int|null $maximumRedirects Maximum number of redirects to follow.
     *
     * @throws RangeException If the number of redirects is negative.
     */
    public function setMaximumRedirects(?int $maximumRedirects): void;

    /**
     * Retrieves the maximum number of redirects the driver will follow before
     * aborting the request. If the driver will not follow any redirects or if
     * it is configured to not have an upper limit on redirects, `NULL` must be
     * returned.
     *
     * @return int|null
     */
    public function getMaximumRedirects(): ?int;
}
