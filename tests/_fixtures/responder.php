<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\StreamInterface;
use Radiergummi\Wander\Http\Status;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

require __DIR__ . '/../../vendor/autoload.php';

$factory = new Psr17Factory();
$creator = new ServerRequestCreator(
    $factory,
    $factory,
    $factory,
    $factory
);
$emitter = new SapiEmitter();


$request = $creator->fromGlobals();
$response = $factory->createResponse(Status::OK);

switch ($request->getUri()->getPath()) {
    case '/loopback':
        $response = $response->withBody(createJsonBody([
            'method' => $request->getMethod(),
            'headers' => $request->getHeaders(),
            'body' => $request->getBody()->getContents(),
            'uri' => $request->getUri()->getPath(),
            'url' => (string)$request->getUri(),
            'query' => $request->getQueryParams()
        ]));
        break;

    default:
        $response = $response
            ->withStatus(Status::NOT_FOUND)
            ->withBody(createJsonBody([
                'error' => 'No test scaffold for URI: ' .
                $request->getUri()->getPath()
            ]));
}

$emitter->emit($response);

/**
 * @internal
 */
function createJsonBody($data): StreamInterface
{
    return Stream::create(json_encode(
        $data,
        JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
    ));
}
