<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Serializers;

use InvalidArgumentException;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Radiergummi\Wander\Interfaces\SerializerInterface;

use RuntimeException;

use function http_build_query;
use function is_array;
use function is_object;
use function parse_str;

class UrlEncodedSerializer implements SerializerInterface
{
    /**
     * Writes URL encoded data to the request body
     *
     * @param RequestInterface $request
     * @param mixed            $body
     *
     * @return RequestInterface
     * @throws InvalidArgumentException
     */
    public function apply(
        RequestInterface $request,
        $body
    ): RequestInterface {
        if ( ! is_array($body) && ! is_object($body)) {
            throw new InvalidArgumentException(
                'Only arrays or objects may be URL encoded'
            );
        }

        $encoded = http_build_query($body);
        $stream = Stream::create($encoded);

        return $request->withBody($stream);
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function extract(ResponseInterface $response)
    {
        $body = $response
            ->getBody()
            ->getContents();

        $data = [];

        parse_str($body, $data);

        return $data;
    }
}
