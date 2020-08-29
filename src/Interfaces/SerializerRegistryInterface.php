<?php

namespace Radiergummi\Wander\Interfaces;

interface SerializerRegistryInterface
{
    /**
     * Registers a new serializer
     *
     * @param string              $mediaType
     * @param SerializerInterface $serializer
     */
    public function register(
        string $mediaType,
        SerializerInterface $serializer
    ): void;

    /**
     * Resolves the matching serializer for a given media type if registered. If
     * no matching serializer can be found, will return `NULL`.
     * This also provides the capability to switch to regular expression matches
     * later on, so serializers can match a range of similar types. In that vein
     * it would also be nice to let serializers define `matches(string $t): bool`
     * and decide for themselves whether they want to match any given type. This
     * would make it necessary to do regex matches in a foreach loop though,
     * which is not particularly well suited to optimization, so I've omitted it
     * for now.
     *
     * @param string $mediaType
     *
     * @return SerializerInterface|null
     */
    public function resolve(string $mediaType): ?SerializerInterface;
}
