<?php

declare(strict_types=1);

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\StreamInterface;
use Radiergummi\Wander\Http\Status;

require __DIR__ . '/../../vendor/autoload.php';

$factory = new Psr17Factory();
$creator = new ServerRequestCreator(
    $factory,
    $factory,
    $factory,
    $factory
);
$emitter = new SapiEmitter();

try {
    $request = $creator->fromGlobals();
    $response = $factory->createResponse(Status::OK);

    switch ($request->getUri()->getPath()) {
        case '/loopback':
            $response = $response->withBody(createJsonBody([
                'method'  => $request->getMethod(),
                'headers' => $request->getHeaders(),
                'body'    => $request->getBody()->getContents(),
                'uri'     => $request->getUri()->getPath(),
                'url'     => (string)$request->getUri(),
                'query'   => $request->getQueryParams(),
            ]));
            break;

        case '/body':
            $response = $response->withBody($request->getBody());
            break;

        default:
            $error = "No handler for URI: {$request->getUri()->getPath()}";
            $response = $response
                ->withStatus(Status::NOT_FOUND)
                ->withBody(createJsonBody([
                    'error' => $error,
                ]));
    }

    $emitter->emit($response);
} catch (Throwable $exception) {
    fwrite(
        STDERR,
        "Failed to handle request: {$exception->getMessage()}"
    );
}

/**
 * @param $data
 *
 * @return StreamInterface
 * @throws InvalidArgumentException
 * @throws JsonException
 * @internal
 */
function createJsonBody($data): StreamInterface
{
    return Stream::create(
        json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
        )
    );
}
