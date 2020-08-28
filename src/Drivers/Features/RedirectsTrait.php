<?php

namespace Radiergummi\Wander\Drivers\Features;

use RangeException;

trait RedirectsTrait
{
    private bool $followRedirects = true;

    private ?int $maximumRedirects = null;

    /**
     * @inheritDoc
     */
    public function followRedirects(bool $followRedirects = true): void
    {
        $this->followRedirects = $followRedirects;
    }

    /*
     * @inheritDoc
     */
    public function isFollowRedirectsEnabled(): bool
    {
        return $this->followRedirects;
    }

    /**
     * @inheritDoc
     */
    public function getMaximumRedirects(): ?int
    {
        return $this->maximumRedirects;
    }

    /**
     * @inheritDoc
     */
    public function setMaximumRedirects(?int $maximumRedirects): void
    {
        if ($maximumRedirects < 0) {
            throw new RangeException(
                'Number of maximum redirects must be 0 or greater'
            );
        }

        $this->maximumRedirects = $maximumRedirects;
    }

}
