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
        // Loopback endpoint: Responds with request information in a JSON body.
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

        // Body endpoint: Mirrors the request body in the response body
        case '/body':
            $requestBody = $request->getBody()->getContents();

            fwrite(
                STDERR,
                "Mirroring request body: '{$requestBody}'\n"
            );

            $response = $response->withBody(Stream::create(
                $requestBody
            ));
            break;

        // Status endpoint: Returns a response with the status code specified in
        // the "code" query parameter.
        case '/status':
            $desiredStatus = (int)($request->getQueryParams()['code'] ?? 200);
            $message = Status::getMessage($desiredStatus) ?: '';

            fwrite(
                STDERR,
                "Responding with {$desiredStatus}: {$message}\n"
            );

            $response = $response
                ->withStatus($desiredStatus)
                ->withBody(Stream::create($message));
            break;

        // By default, a 404 error will be generated.
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
