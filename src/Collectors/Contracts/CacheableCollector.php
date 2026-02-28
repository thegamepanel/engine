<?php

namespace Engine\Collectors\Contracts;

/**
 * @template TCached of array
 */
interface CacheableCollector
{
    /**
     * Load the collector from the given cache data.
     *
     * @param TCached|array<mixed> $data
     *
     * @return void
     */
    public function fromCache(array $data): void;

    /**
     * Convert the collector to a cacheable array.
     *
     * @return TCached|array<mixed>
     */
    public function toCache(): array;
}
