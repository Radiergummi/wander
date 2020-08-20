<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends WanderException implements ClientExceptionInterface
{
}
