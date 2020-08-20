<?php

declare(strict_types=1);

use Radiergummi\Wander\Interfaces\BodyInterface;

class UnEncodedBody implements BodyInterface
{
    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
    }
}
