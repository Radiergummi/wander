<?php

namespace Radiergummi\Wander;

use Radiergummi\Wander\Interfaces\SerializerInterface;
use Radiergummi\Wander\Interfaces\SerializerRegistryInterface;

class SerializerRegistry implements SerializerRegistryInterface
{
    /**
     * @var array<string, SerializerInterface>
     */
    protected array $serializers = [];

    /**
     * @inheritDoc
     */
    public function register(
        string $mediaType,
        SerializerInterface $serializer
    ): void {
        $this->serializers[$mediaType] = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $mediaType): ?SerializerInterface
    {
        return $this->serializers[$mediaType] ?? null;
    }
}
