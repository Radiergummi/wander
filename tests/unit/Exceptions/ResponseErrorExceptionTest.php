<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Unit\Exceptions;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Radiergummi\Wander\Exceptions\ResponseErrorException;

class ResponseErrorExceptionTest extends TestCase
{
    public function testGetRequest(): void
    {
        $request = new Request('', '');
        $response = new Response(400);
        $exception = new ResponseErrorException($request, $response);

        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($response, $exception->getResponse());
    }
}
