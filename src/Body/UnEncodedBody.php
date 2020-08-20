<?php

declare(strict_types=1);

use Radiergummi\Wander\Interfaces\BodyInterface;

class UnEncodedBody implements BodyInterface
{
    /**
     * UnEncodedBody constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
    }
}
