<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Serializers;

use InvalidArgumentException;
use JsonException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Radiergummi\Wander\Interfaces\SerializerInterface;

use RuntimeException;

use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

class JsonSerializer implements SerializerInterface
{
    protected ?int $encodeFlags;

    protected ?int $decodeFlags;

    public function __construct(
        ?int $encodeFlags = null,
        ?int $decodeFlags = null
    ) {
        $this->encodeFlags = $encodeFlags;
        $this->decodeFlags = $decodeFlags;
    }

    /**
     * Writes JSON data to the request body
     *
     * @param RequestInterface $request
     * @param mixed            $body
     *
     * @return RequestInterface
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function apply(
        RequestInterface $request,
        $body
    ): RequestInterface {
        $encoded = json_encode(
            $body,
            ($this->encodeFlags ?? 0) | JSON_THROW_ON_ERROR
        );

        $stream = Stream::create($encoded);

        return $request->withBody($stream);
    }

    /**
     * @inheritDoc
     * @throws JsonException
     * @throws RuntimeException
     */
    public function extract(ResponseInterface $response)
    {
        $body = $response
            ->getBody()
            ->getContents();

        return json_decode(
            $body,
            true,
            512,
            ($this->decodeFlags ?? 0) | JSON_THROW_ON_ERROR
        );
    }
}
