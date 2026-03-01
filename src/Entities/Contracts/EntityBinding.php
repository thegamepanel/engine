<?php

namespace Engine\Entities\Contracts;

/**
 * @template TEntity of object
 */
interface EntityBinding
{
    /**
     * Resolve an entity based on the provided key and context.
     *
     * @param string               $key
     * @param array<string, mixed> $context
     *
     * @return TEntity|null
     */
    public function resolve(string $key, array $context): ?object;

    /**
     * Get the key for the given entity.
     *
     * @param TEntity $entity
     *
     * @return string
     */
    public function key(object $entity): string;
}
